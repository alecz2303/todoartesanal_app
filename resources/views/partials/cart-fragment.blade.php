@if(empty($cart))
    <div class="bg-white rounded-2xl p-6 border text-slate-600">
        Tu carrito está vacío. <a class="underline" href="{{ route('home') }}">Ir a la tienda</a>
    </div>
@else
@php($total = 0)

<form id="cart-form" action="{{ route('cart.update') }}" method="POST" class="space-y-4">
    @csrf

    @foreach($cart as $id => $item)
    @php($qty = (int) ($item['qty'] ?? 1))
    @php($priceCents = (int) ($item['price_cents'] ?? 0))
    @php($subtotal = $priceCents * $qty)
    @php($total += $subtotal)

    <div class="bg-white rounded-2xl shadow p-4 flex gap-4 items-center">
        @if(!empty($item['cover_image_path']))
            <img src="{{ asset('storage/' . $item['cover_image_path']) }}" class="w-20 h-20 object-cover rounded-xl" alt="">
        @else
            <div class="w-20 h-20 bg-slate-100 rounded-xl"></div>
        @endif

        <div class="flex-1">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold leading-tight">{{ $item['name'] ?? 'Producto' }}</h2>
                    <p class="text-slate-500 text-sm">
                        ${{ number_format($priceCents / 100, 2) }} c/u
                    </p>
                </div>

                <div class="text-right">
                    <p class="font-black text-lg">
                        ${{ number_format($subtotal / 100, 2) }}
                    </p>
                    <p class="text-xs text-slate-500">Subtotal</p>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button type="button" class="w-10 h-10 rounded-xl border hover:bg-slate-50"
                        onclick="decQty('{{ $id }}'); scheduleCartUpdate();">−</button>

                    <input id="qty-{{ $id }}" name="quantities[{{ $id }}]" type="number" min="0" max="99"
                        class="w-20 text-center border rounded-xl py-2 transition-transform" value="{{ $qty }}"
                        oninput="scheduleCartUpdate()">

                    <button type="button" class="w-10 h-10 rounded-xl border hover:bg-slate-50"
                        onclick="incQty('{{ $id }}'); scheduleCartUpdate();">+</button>
                </div>

                <button type="button"
                    class="text-sm px-3 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-50"
                    onclick="removeItem('{{ $id }}')">
                    Quitar
                </button>
            </div>
        </div>
    </div>
    @endforeach

    <div class="bg-white rounded-2xl border p-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-slate-500 text-sm">Total</p>
            <p class="text-3xl font-black">${{ number_format($total / 100, 2) }}</p>
            <p id="saving-indicator" class="hidden mt-2 text-sm text-slate-500">Guardando cambios…</p>
        </div>
        <a href="{{ route('checkout') }}"
            class="px-5 py-3 rounded-xl bg-[var(--ta-pink)] hover:bg-[var(--ta-purple)] text-white font-black text-center transition shadow-lg">
            Continuar a checkout
        </a>

        <div class="flex gap-3">
            <button class="px-5 py-3 rounded-xl border hover:bg-slate-50" type="submit">
                Actualizar carrito
            </button>

            <a href="{{ route('home') }}" class="px-5 py-3 rounded-xl bg-black text-white text-center hover:opacity-90">
                Seguir comprando
            </a>
        </div>
    </div>
</form>
@endif