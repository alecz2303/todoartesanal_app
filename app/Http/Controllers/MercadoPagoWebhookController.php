<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Webhook típico: type + data.id (payment id)
        $paymentId = $request->input('data.id') ?? $request->input('id');

        if (!$paymentId) {
            return response()->json(['ok' => true]);
        }

        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
        $paymentClient = new PaymentClient();

        $payment = $paymentClient->get((int) $paymentId);
        $orderId = $payment->external_reference ?? null;

        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $order->update([
                    'mp_payment_id' => (string) $paymentId,
                    'mp_status' => (string) ($payment->status ?? ''),
                    'status' => ($payment->status === 'approved') ? 'paid' : (($payment->status === 'rejected') ? 'failed' : 'pending'),
                ]);

                if ($payment->status === 'approved') {
                    // Si quieres: vaciar carrito al pago aprobado
                    // (aquí no sabemos la sesión, pero puedes vaciar en success page)
                }
            }
        }

        return response()->json(['ok' => true]);
    }
}