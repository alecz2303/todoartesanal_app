<x-layouts.app title="Checkout | Todo Artesanal Tuxtla">

@php
    $total = 0;
    foreach ($cart as $item) {
        $total += ((int)$item['price_cents']) * ((int)$item['qty']);
    }

    $bank = config('bank');
@endphp

<div class="max-w-5xl mx-auto">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl md:text-4xl font-black tracking-tight">
                Checkout
            </h1>

            <p class="text-slate-600 mt-2">
                Completa tus datos para continuar con el pago.
            </p>
        </div>

        <a href="{{ route('cart') }}"
           class="px-4 py-2 rounded-xl border bg-white/70 backdrop-blur hover:bg-white transition">
            ← Volver al carrito
        </a>
    </div>


    <div class="grid lg:grid-cols-3 gap-6">

        {{-- FORM --}}
        <div class="lg:col-span-2">

            <form id="checkout-form"
                  action="{{ route('checkout.start') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="bg-white/85 backdrop-blur rounded-2xl shadow-lg border p-6">

                @csrf


                {{-- DATOS CLIENTE --}}
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-black text-[var(--ta-dark)]">
                        Tus datos
                    </h2>

                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-[var(--ta-yellow)] text-black">
                        Pedido en 1 minuto ⚡
                    </span>
                </div>


                <div class="grid md:grid-cols-2 gap-4">

                    <div>
                        <label class="text-sm font-semibold">Nombre *</label>

                        <input name="name"
                               value="{{ old('name') }}"
                               class="mt-1 w-full rounded-xl border px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--ta-pink)]"
                               placeholder="Ej. Karelly">

                        @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>


                    <div>
                        <label class="text-sm font-semibold">WhatsApp *</label>

                        <input name="phone"
                               value="{{ old('phone') }}"
                               class="mt-1 w-full rounded-xl border px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--ta-pink)]"
                               placeholder="Ej. 961 000 0000">

                        @error('phone')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>


                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold">Correo (opcional)</label>

                        <input name="email"
                               value="{{ old('email') }}"
                               class="mt-1 w-full rounded-xl border px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--ta-pink)]"
                               placeholder="Ej. correo@dominio.com">

                        @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>


                <hr class="my-6">


                {{-- ENTREGA --}}
                <h2 class="text-xl font-black text-[var(--ta-dark)] mb-3">
                    Entrega
                </h2>


                <div class="grid md:grid-cols-2 gap-3">

                    <label class="cursor-pointer rounded-2xl border p-4 bg-white hover:border-[var(--ta-pink)] transition">

                        <input type="radio"
                               name="delivery"
                               value="pickup"
                               class="mr-2"
                               {{ old('delivery','pickup') === 'pickup' ? 'checked' : '' }}
                               onclick="toggleAddress(false)">

                        <span class="font-bold">Recoger en tienda</span>

                        <p class="text-sm text-slate-600 mt-1">
                            Ideal si estás en Tuxtla. Coordinamos por WhatsApp.
                        </p>

                    </label>


                    <label class="cursor-pointer rounded-2xl border p-4 bg-white hover:border-[var(--ta-pink)] transition">

                        <input type="radio"
                               name="delivery"
                               value="shipping"
                               class="mr-2"
                               {{ old('delivery') === 'shipping' ? 'checked' : '' }}
                               onclick="toggleAddress(true)">

                        <span class="font-bold">Envío / Entrega</span>

                        <p class="text-sm text-slate-600 mt-1">
                            Comparte tu dirección y te cotizamos envío.
                        </p>

                    </label>

                    @error('delivery')
                    <p class="text-sm text-red-600 md:col-span-2">{{ $message }}</p>
                    @enderror

                </div>


                {{-- DIRECCION --}}
                <div id="address-box"
                     class="mt-4 {{ old('delivery') === 'shipping' ? '' : 'hidden' }}">

                    <label class="text-sm font-semibold">
                        Dirección (si aplica)
                    </label>

                    <input name="address"
                           value="{{ old('address') }}"
                           class="mt-1 w-full rounded-xl border px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--ta-pink)]"
                           placeholder="Calle, colonia, referencias…">

                    @error('address')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror

                </div>



                {{-- NOTAS --}}
                <div class="mt-4">
                    <label class="text-sm font-semibold">Notas (opcional)</label>

                    <textarea name="notes"
                              rows="3"
                              class="mt-1 w-full rounded-xl border px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--ta-pink)]"
                              placeholder="Ej. Colores, tema, fecha del evento...">{{ old('notes') }}</textarea>

                    @error('notes')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>



                {{-- POLITICAS --}}
                <div class="mt-6 rounded-2xl border bg-[var(--ta-bg)] p-4">

                    <p class="text-sm text-slate-700">

                        <span class="font-bold">Políticas:</span>

                        pedidos con <span class="font-bold">20 días de anticipación</span>
                        y <span class="font-bold">50% de anticipo</span> para agendar.

                    </p>

                </div>



                {{-- METODO PAGO --}}
                <h2 class="text-xl font-black text-[var(--ta-dark)] mb-3 mt-6">
                    Método de pago
                </h2>


                <div class="grid md:grid-cols-2 gap-3">

                    {{-- MP --}}
                    <label class="cursor-pointer rounded-2xl border p-4 bg-white hover:border-[var(--ta-pink)] transition">

                        <input type="radio"
                               name="payment_method"
                               value="mp"
                               class="mr-2"
                               checked
                               onclick="toggleTransfer(false)">

                        <span class="font-bold">Mercado Pago</span>

                        <p class="text-sm text-slate-600 mt-1">
                            Tarjeta, transferencia, tiendas, etc.
                        </p>

                    </label>


                    {{-- TRANSFER --}}
                    <label class="cursor-pointer rounded-2xl border p-4 bg-white hover:border-[var(--ta-pink)] transition">

                        <input type="radio"
                               name="payment_method"
                               value="transfer"
                               class="mr-2"
                               onclick="toggleTransfer(true)">

                        <span class="font-bold">Transferencia bancaria</span>

                        <p class="text-sm text-slate-600 mt-1">
                            Te mostramos los datos y confirmas tu pago.
                        </p>

                    </label>

                </div>



                {{-- DATOS TRANSFERENCIA --}}
                <div id="transfer-box"
                     class="hidden mt-4 rounded-2xl border bg-white/70 backdrop-blur p-5">

                    <p class="font-black text-[var(--ta-purple)]">
                        Datos para transferencia
                    </p>

                    <div class="mt-4 grid gap-2 text-sm">

                        <div>
                            <span class="font-semibold">Banco:</span>
                            {{ $bank['bank'] ?? '' }}
                        </div>

                        <div>
                            <span class="font-semibold">Titular:</span>
                            {{ $bank['holder'] ?? '' }}
                        </div>

                        <div class="flex items-center gap-2">

                            <span class="font-semibold">CLABE:</span>

                            <span id="bank-clabe" class="font-mono">
                                {{ $bank['clabe'] ?? '' }}
                            </span>

                            <button type="button"
                                    class="text-xs px-2 py-1 rounded-lg border"
                                    onclick="copyText(document.getElementById('bank-clabe').innerText)">
                                Copiar
                            </button>

                        </div>


                        <div class="flex items-center gap-2">

                            <span class="font-semibold">Cuenta:</span>

                            <span id="bank-account" class="font-mono">
                                {{ $bank['account'] ?? '' }}
                            </span>

                            <button type="button"
                                    class="text-xs px-2 py-1 rounded-lg border"
                                    onclick="copyText(document.getElementById('bank-account').innerText)">
                                Copiar
                            </button>

                        </div>

                        <div>
                            <span class="font-semibold">Referencia:</span>
                            Se genera con tu número de orden
                        </div>

                    </div>

                </div>



                {{-- BOTON PAGAR --}}
                <div class="mt-6 flex flex-col md:flex-row gap-3">

                    <button id="pay-btn"
                            type="submit"
                            class="flex-1 text-center rounded-2xl bg-[var(--ta-pink)] hover:bg-[var(--ta-purple)] text-white px-6 py-4 font-black transition shadow">

                        Continuar al pago

                    </button>


                    <a href="{{ route('home') }}"
                       class="flex-1 text-center rounded-2xl border bg-white hover:bg-white/70 px-6 py-4 font-bold transition">
                        Seguir comprando
                    </a>

                </div>


                <p id="processing"
                   class="hidden mt-3 text-sm text-slate-600">

                    Procesando… no cierres esta ventana ✨

                </p>

            </form>

        </div>



        {{-- RESUMEN --}}
        <div class="lg:col-span-1">

            <div class="bg-white/85 backdrop-blur rounded-2xl shadow-lg border p-6 sticky top-24">

                <h2 class="text-xl font-black mb-4">
                    Resumen
                </h2>


                <div class="space-y-3 max-h-[45vh] overflow-auto pr-2">

                    @foreach($cart as $item)

                        @php
                            $qty = (int)$item['qty'];
                            $price = (int)$item['price_cents'];
                            $line = $qty * $price;
                        @endphp

                        <div class="flex gap-3 items-center">

                            <div class="w-12 h-12 rounded-xl bg-white border overflow-hidden shrink-0">

                                @if(!empty($item['cover_image_path']))
                                    <img src="{{ asset('storage/'.$item['cover_image_path']) }}"
                                         class="w-full h-full object-cover">
                                @endif

                            </div>

                            <div class="flex-1">
                                <p class="font-semibold leading-tight">
                                    {{ $item['name'] }}
                                </p>

                                <p class="text-xs text-slate-500">
                                    Cantidad: {{ $qty }}
                                </p>
                            </div>


                            <div class="text-right font-black">
                                ${{ number_format($line/100,2) }}
                            </div>

                        </div>

                    @endforeach

                </div>


                <hr class="my-4">


                <div class="flex items-center justify-between">

                    <span class="text-slate-600 font-semibold">
                        Total
                    </span>

                    <span class="text-2xl font-black">
                        ${{ number_format($total/100,2) }}
                    </span>

                </div>

            </div>

        </div>


    </div>

</div>


<script>

function toggleAddress(show){
    const box = document.getElementById('address-box');
    box.classList.toggle('hidden', !show);
}

function toggleTransfer(show){
    const box = document.getElementById('transfer-box');
    box.classList.toggle('hidden', !show);
}

function copyText(text){
    navigator.clipboard.writeText(text);
}

document.getElementById('checkout-form')?.addEventListener('submit', () => {

    document.getElementById('processing')?.classList.remove('hidden');

    const btn = document.getElementById('pay-btn');

    if(btn){
        btn.disabled = true;
        btn.style.opacity = "0.8";
        btn.innerText = "Procesando...";
    }

});

</script>


</x-layouts.app>