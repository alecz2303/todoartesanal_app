<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class CheckoutController extends Controller
{
    public function start(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart))
            return back()->with('error', 'Tu carrito está vacío.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'delivery' => ['required', 'in:pickup,shipping'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        // Total + items snapshot
        $total = 0;

        $order = Order::create([
            'status' => 'pending',
            'total_cents' => 0,
            ...$data,
        ]);

        foreach ($cart as $productId => $row) {
            $product = Product::whereKey($productId)->where('is_active', true)->firstOrFail();
            $qty = max(1, (int) ($row['qty'] ?? 1));

            $total += $product->price_cents * $qty;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'name_snapshot' => $product->name,
                'price_cents_snapshot' => $product->price_cents,
                'qty' => $qty,
            ]);
        }

        $order->update(['total_cents' => $total]);

        // Guardamos id de orden para el siguiente paso
        session()->put('checkout_order_id', $order->id);

        return redirect()->route('checkout.mp');
    }

    public function mercadoPago()
    {
        $orderId = session('checkout_order_id');
        abort_if(!$orderId, 404);

        $order = Order::with('items')->findOrFail($orderId);

        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));

        $items = $order->items->map(function ($it) {
            return [
                'title' => $it->name_snapshot,
                'quantity' => (int) $it->qty,
                'unit_price' => (float) ($it->price_cents_snapshot / 100),
                'currency_id' => 'MXN',
            ];
        })->values()->all();

        $client = new PreferenceClient();

        $preference = $client->create([
            'items' => $items,
            'external_reference' => (string) $order->id,
            'back_urls' => [
                'success' => route('checkout.success', ['order' => $order->id]),
                'failure' => route('checkout.failure', ['order' => $order->id]),
                'pending' => route('checkout.pending', ['order' => $order->id]),
            ],
            'auto_return' => 'approved',
            'notification_url' => route('mp.webhook'),
        ]);

        $order->update(['mp_preference_id' => $preference->id ?? null]);

        // Redirige a MercadoPago
        return redirect()->away($preference->init_point);
    }

    public function success(Request $request)
    {
        return view('checkout-success');
    }
    public function failure(Request $request)
    {
        return view('checkout-failure');
    }
    public function pending(Request $request)
    {
        return view('checkout-pending');
    }
}