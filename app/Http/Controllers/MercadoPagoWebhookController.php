<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // MP puede mandar:
        // - Webhooks: { data: { id: 123 } }
        // - o query id=123
        $paymentId =
            $request->input('data.id') ??
            ($request->input('data')['id'] ?? null) ??
            $request->input('id') ??
            $request->query('data.id') ??
            $request->query('id');

        if (!$paymentId) {
            return response()->json(['ok' => true, 'ignored' => 'no_payment_id'], 200);
        }

        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));

        try {
            $paymentClient = new PaymentClient();
            $payment = $paymentClient->get((int) $paymentId);

            $orderId = $payment->external_reference ?? null;
            if (!$orderId) {
                return response()->json(['ok' => true, 'ignored' => 'no_external_reference'], 200);
            }

            $order = Order::find($orderId);
            if (!$order) {
                return response()->json(['ok' => true, 'ignored' => 'order_not_found'], 200);
            }

            // Solo si la orden es Mercado Pago
            if ($order->payment_method !== PaymentMethod::MercadoPago) {
                return response()->json(['ok' => true, 'ignored' => 'not_mp_order'], 200);
            }

            $mpStatus = (string) ($payment->status ?? '');

            // Guardar evidencia MP
            $order->mp_payment_id = (string) ($payment->id ?? $paymentId);
            $order->mp_status = $mpStatus;

            // Map status MP -> status Order
            if ($mpStatus === 'approved') {
                $order->status = OrderStatus::Paid;
                $order->paid_at = now();
            } elseif (in_array($mpStatus, ['rejected', 'cancelled'], true)) {
                $order->status = OrderStatus::Failed;
            } else {
                // pending / in_process / authorized / etc
                $order->status = OrderStatus::PendingPayment;
            }

            $order->save();

            return response()->json(['ok' => true], 200);
        } catch (\Throwable $e) {
            Log::error('MercadoPago webhook error', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            // MVP: responde 200 para que no se “atoren” reintentos infinitos
            return response()->json(['ok' => true], 200);
        }
    }
}