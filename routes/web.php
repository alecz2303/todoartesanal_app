<?php

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MercadoPagoWebhookController;

Route::get('/', function () {
    $products = Product::where('is_active', true)->latest()->get();
    return view('home', compact('products'));
})->name('home');

Route::get('/p/{slug}', function ($slug) {
    $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
    return view('product', compact('product'));
})->name('product.show');

Route::post('/cart/add/{id}', function (Request $request, $id) {
    $product = Product::whereKey($id)->where('is_active', true)->firstOrFail();

    $cart = session()->get('cart', []);
    $key = (string) $id;

    if (isset($cart[$key])) {
        $cart[$key]['qty']++;
    } else {
        $cart[$key] = [
            'name' => $product->name,
            'price_cents' => $product->price_cents,
            'cover_image_path' => $product->cover_image_path,
            'qty' => 1,
        ];
    }

    session()->put('cart', $cart);

    $count = collect($cart)->sum('qty');

    if ($request->expectsJson()) {
        return response()->json([
            'ok' => true,
            'message' => 'Agregado ✅',
            'cart_count' => $count,
        ]);
    }

    return back()->with('success', 'Producto agregado al carrito');
})->name('cart.add');

Route::get('/cart', function () {
    $cart = session('cart', []);
    return view('cart', compact('cart'));
})->name('cart');

Route::get('/cart/fragment', function () {
    $cart = session('cart', []);
    return view('partials.cart-fragment', compact('cart'));
})->name('cart.fragment');

Route::post('/cart/update', function (Request $request) {
    $cart = session('cart', []);
    $quantities = $request->input('quantities', []);

    foreach ($quantities as $id => $qty) {
        $id = (string) $id;
        $qty = (int) $qty;

        if (!isset($cart[$id]))
            continue;

        if ($qty <= 0) {
            unset($cart[$id]);
        } else {
            $cart[$id]['qty'] = min($qty, 99);
        }
    }session()->put('cart', $cart);

    $count = collect($cart)->sum('qty');

    if ($request->expectsJson()) {
        $html = view('partials.cart-fragment', ['cart' => $cart])->render();

        return response()->json([
            'ok' => true,
            'message' => 'Carrito actualizado ✅',
            'cart_count' => $count,
            'html' => $html,
        ]);
    }

    return back()->with('success', 'Carrito actualizado');
})->name('cart.update');

Route::post('/cart/clear', function (Request $request) {
    session()->forget('cart');
    $count = 0;

    if ($request->expectsJson()) {
        $html = view('partials.cart-fragment', ['cart' => []])->render();

        return response()->json([
            'ok' => true,
            'message' => 'Carrito vaciado ✅',
            'cart_count' => $count,
            'html' => $html,
        ]);
    }

    return back()->with('success', 'Carrito vaciado');
})->name('cart.clear');

// Checkout visual
Route::get('/checkout', function () {
    $cart = session('cart', []);
    abort_if(empty($cart), 404);
    return view('checkout', compact('cart'));
})->name('checkout');

Route::post('/checkout/start', [CheckoutController::class, 'start'])->name('checkout.start');

// MercadoPago
Route::get('/checkout/mp', [CheckoutController::class, 'mercadoPago'])->name('checkout.mp');

Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/failure', [CheckoutController::class, 'failure'])->name('checkout.failure');
Route::get('/checkout/pending', [CheckoutController::class, 'pending'])->name('checkout.pending');

Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])->name('mp.webhook');