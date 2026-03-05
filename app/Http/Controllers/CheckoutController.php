<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class CheckoutController extends Controller
{
    public function start(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('checkout')->with('error', 'Tu carrito está vacío.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],

            'delivery' => ['required', 'in:pickup,shipping'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],

            'payment_method' => ['required', 'in:mp,transfer'],
        ]);

        if ($data['delivery'] === 'shipping' && blank($data['address'])) {
            return back()->withErrors(['address' => 'La dirección es requerida para envío.'])->withInput();
        }

        $order = DB::transaction(function () use ($data, $cart) {
            $order = Order::create([
                'status' => $data['payment_method'] === 'mp'
                    ? OrderStatus::PendingPayment
                    : OrderStatus::PendingTransfer,

                'payment_method' => $data['payment_method'] === 'mp'
                    ? PaymentMethod::MercadoPago
                    : PaymentMethod::Transfer,

                'total_cents' => 0,

                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,

                'delivery' => $data['delivery'],
                'address' => $data['address'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($cart as $productId => $item) {
                $qty = (int) ($item['qty'] ?? 1);
                $priceCents = (int) ($item['price_cents'] ?? 0);
                $name = (string) ($item['name'] ?? 'Producto');

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => (int) $productId,
                    'name_snapshot' => $name,
                    'price_cents_snapshot' => $priceCents,
                    'qty' => max(1, $qty),
                ]);
            }

            $order->recalcTotal();

            session(['last_order_id' => $order->id]);

            return $order;
        });

        // MVP: orden ya creada, vaciamos carrito
        session()->forget('cart');

        if ($order->payment_method === PaymentMethod::Transfer) {
            return redirect()->route('transfer.instructions', $order);
        }

        // Mercado Pago
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));

        $items = $order->items->map(function ($i) {
            return [
                'title' => $i->name_snapshot,
                'quantity' => $i->qty,
                'unit_price' => round($i->price_cents_snapshot / 100, 2),
                'currency_id' => 'MXN',
            ];
        })->values()->all();

        $client = new PreferenceClient();

        $preference = $client->create([
            'items' => $items,
            'external_reference' => (string) $order->id,
            'statement_descriptor' => 'TODO ARTESANAL',
            'back_urls' => [
                'success' => route('checkout.success', $order),
                'failure' => route('checkout.failure', $order),
                'pending' => route('checkout.pending', $order),
            ],
            'auto_return' => 'approved',
            'notification_url' => route('mp.webhook') . '?source_news=webhooks',
        ]);

        $order->update([
            'mp_preference_id' => $preference->id ?? null,
        ]);

        return redirect()->away($preference->init_point);
    }

    public function success(Order $order)
    {
        return view('checkout.success', compact('order'));
    }

    public function failure(Order $order)
    {
        return view('checkout.failure', compact('order'));
    }

    public function pending(Order $order)
    {
        return view('checkout.pending', compact('order'));
    }
}