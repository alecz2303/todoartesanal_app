<x-layouts.app title="Carrito | Todo Artesanal Tuxtla">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl md:text-3xl font-black">🛒 Tu carrito</h1>

        <button id="btn-clear-cart" class="text-sm px-3 py-2 rounded-lg border hover:bg-slate-50" type="button">
            Vaciar carrito
        </button>
    </div>

    <div id="cart-root">
        @include('partials.cart-fragment', ['cart' => $cart])
    </div>
</x-layouts.app>