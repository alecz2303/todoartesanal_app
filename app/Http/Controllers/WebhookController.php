<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class WebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        // Mercado Pago puede mandar:
        // Webhooks: { "type": "...", "data": { "id": "123" } }
        // IPN: query topic=payment&id=123 (evítalo con source_news=webhooks)
        $paymentId = $request->input('data.id')
            ?? $request->input('data')['id'] ?? null
            ?? $request->query('data.id')
            ?? $request->query('id');

        if (!$paymentId) {
            return response()->json(['ok' => true, 'ignored' => 'no_payment_id'], 200);
        }

        // (Opcional) Verificación x-signature si configuraste secret
        // Si no hay secret, igual estás seguro porque la fuente de verdad es consultar Payment API.
        if ($secret = config('services.mercadopago.webhook_secret')) {
            if (!$this->verifySignature($request, (string) $paymentId, $secret)) {
                return response()->json(['error' => 'invalid_signature'], 401);
            }
        }

        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));

        try {
            $client = new PaymentClient();
            $payment = $client->get((int) $paymentId);

            $orderId = $payment->external_reference ?? null;
            if (!$orderId) {
                return response()->json(['ok' => true, 'ignored' => 'no_external_reference'], 200);
            }

            $order = Order::find($orderId);
            if (!$order) {
                return response()->json(['ok' => true, 'ignored' => 'order_not_found'], 200);
            }

            // Solo actualizar si el pedido es MP
            if ($order->payment_method !== PaymentMethod::MercadoPago) {
                return response()->json(['ok' => true, 'ignored' => 'not_mp_order'], 200);
            }

            $mpStatus = (string) ($payment->status ?? '');

            $order->mp_payment_id = (string) ($payment->id ?? $paymentId);
            $order->mp_status = $mpStatus;

            // Map de status MP -> status Order
            // (approved = pagado)
            if ($mpStatus === 'approved') {
                $order->status = OrderStatus::Paid;
                $order->paid_at = now();
            } elseif (in_array($mpStatus, ['rejected', 'cancelled'], true)) {
                $order->status = OrderStatus::Failed;
            } else {
                // pending / in_process / authorized etc.
                $order->status = OrderStatus::PendingPayment;
            }

            $order->save();

            return response()->json(['ok' => true], 200);
        } catch (\Throwable $e) {
            Log::error('MP webhook error', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            // Responder 200 evita reintentos infinitos si fue un error temporal tuyo (MVP)
            return response()->json(['ok' => true], 200);
        }
    }

    private function verifySignature(Request $request, string $paymentId, string $secret): bool
    {
        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');

        if (!$xSignature || !$xRequestId) return false;

        // x-signature: "ts=...,v1=..."
        $parts = collect(explode(',', $xSignature))
            ->map(fn ($p) => array_map('trim', explode('=', $p, 2)))
            ->filter(fn ($kv) => count($kv) === 2)
            ->mapWithKeys(fn ($kv) => [$kv[0] => $kv[1]]);

        $ts = $parts->get('ts');
        $hash = $parts->get('v1');

        if (!$ts || !$hash) return false;

        // Basado en el formato de manifest usado por MP en ejemplos/comunidad
        // manifest = `id:${id};request-id:${requestId};ts:${ts};`
        $manifest = "id:{$paymentId};request-id:{$xRequestId};ts:{$ts};";

        $computed = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($computed, $hash);
    }
}