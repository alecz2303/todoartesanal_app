<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Todo Artesanal Tuxtla' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --ta-pink: #ff2f92;
            --ta-pink-light: #ff66b2;
            --ta-purple: #6a00b8;
            --ta-green: #32d74b;
            --ta-yellow: #ffd60a;
            --ta-dark: #2b0038;
            --ta-bg: #fff0f7;
        }

        body {
            background: var(--ta-bg);
            position: relative;
            overflow-x: hidden;
        }

        /* CAPA 1 – manchas principales más intensas */
        body::before {
            content: "";
            position: fixed;
            inset: -15%;
            z-index: -1;
            pointer-events: none;
            opacity: .85;
            filter: blur(12px) saturate(1.3);
            background:
                radial-gradient(closest-side at 15% 15%, rgba(255, 47, 146, .75), transparent 60%),
                radial-gradient(closest-side at 85% 20%, rgba(106, 0, 184, .65), transparent 65%),
                radial-gradient(closest-side at 75% 85%, rgba(255, 102, 178, .55), transparent 60%),
                radial-gradient(closest-side at 10% 80%, rgba(255, 214, 10, .45), transparent 55%);
        }

        /* CAPA 2 – acentos vivos */
        body::after {
            content: "";
            position: fixed;
            inset: -10%;
            z-index: -1;
            pointer-events: none;
            opacity: .65;
            filter: blur(18px) saturate(1.4);
            background:
                radial-gradient(closest-side at 80% 65%, rgba(50, 215, 75, .45), transparent 60%),
                radial-gradient(closest-side at 35% 55%, rgba(255, 214, 10, .35), transparent 55%),
                radial-gradient(closest-side at 55% 10%, rgba(255, 47, 146, .35), transparent 60%);
        }

        /* Movimiento más visible */
        @keyframes floaty {
            0% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            50% {
                transform: translate3d(2%, -2%, 0) scale(1.05);
            }

            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }
        }

        body::before {
            animation: floaty 16s ease-in-out infinite;
        }

        body::after {
            animation: floaty 22s ease-in-out infinite reverse;
        }

        @keyframes cart-shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20% {
                transform: translateX(-4px);
            }

            40% {
                transform: translateX(4px);
            }

            60% {
                transform: translateX(-3px);
            }

            80% {
                transform: translateX(3px);
            }
        }

        .cart-shake {
            animation: cart-shake 320ms ease-in-out;
        }
    </style>
</head>

<body class="bg-pink-50 text-slate-900">

    <header class="sticky top-0 z-50 bg-white shadow-md">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">

            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo-todo-artesanal.png') }}" class="h-12 w-auto" alt="Todo Artesanal">
            </a>

            <nav class="flex items-center gap-4">

                <a href="{{ route('home') }}"
                    class="font-semibold text-[var(--ta-purple)] hover:text-[var(--ta-pink)] transition">
                    Tienda
                </a>

                <a id="cart-link" href="{{ route('cart') }}"
                    class="relative bg-[var(--ta-pink)] text-white px-4 py-2 rounded-xl font-semibold hover:bg-[var(--ta-purple)] transition">

                    🛒 Carrito

                    @php($count = collect(session('cart', []))->sum('qty'))
                    <span id="cart-count" class="{{ $count > 0 ? '' : 'hidden' }}
                        absolute -top-2 -right-2 bg-[var(--ta-yellow)]
                        text-black text-xs font-bold rounded-full px-2 py-1">
                        {{ $count }}
                    </span>
                </a>

            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8">
        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="border-t bg-white">
        <div
            class="max-w-6xl mx-auto px-6 py-6 text-sm text-slate-500 flex flex-col md:flex-row gap-2 md:items-center md:justify-between">
            <span>© {{ date('Y') }} Todo Artesanal Tuxtla</span>
            <span>Hecho con 💖 en Chiapas</span>
        </div>
    </footer>

    <div id="toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 hidden z-[9999]
            bg-[var(--ta-purple)] text-white px-5 py-3 rounded-xl shadow-xl
            text-sm font-semibold opacity-0 transition-all duration-200">
    </div>

    <script>
        // ===== Helpers globales =====
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        function toast(msg = 'Listo ✅') {
            const el = document.getElementById('toast');
            if (!el) return;

            el.textContent = msg;
            el.classList.remove('hidden');
            requestAnimationFrame(() => {
                el.classList.remove('opacity-0');
                el.classList.add('opacity-100', 'translate-y-0');
            });

            clearTimeout(window.__toastTimer);
            window.__toastTimer = setTimeout(() => {
                el.classList.add('opacity-0');
                el.classList.remove('opacity-100');
                setTimeout(() => el.classList.add('hidden'), 180);
            }, 1200);
        }

        function popCartCount() {
            const el = document.getElementById('cart-count');
            if (!el) return;
            el.classList.add('scale-125');
            setTimeout(() => el.classList.remove('scale-125'), 140);
        }

        function setCartCount(n) {
            const el = document.getElementById('cart-count');
            if (!el) return;

            if (n > 0) {
                el.textContent = n;
                el.classList.remove('hidden');
                popCartCount();
            } else {
                el.textContent = '0';
                el.classList.add('hidden');
            }
        }

        // Intercepta TODOS los forms que tengan data-ajax="add-to-cart"
        document.addEventListener('submit', async (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (form.dataset.ajax !== 'add-to-cart') return;

            e.preventDefault();

            // ✅ Fly-to-cart desde Home o Product
            const key = form.dataset.flyFrom; // viene del home
            let img = null;

            if (key) {
                img = document.querySelector(`[data-fly-img="${key}"]`);
            } else {
                // fallback para product
                img = document.getElementById('product-image');
            }

            if (img) flyToCartFrom(img);

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();

                if (data.ok) {
                    setCartCount(data.cart_count ?? 0);
                    toast(data.message || 'Agregado ✅');
                    shakeCart();
                } else {
                    toast('Ups… intenta otra vez');
                }
            } catch (err) {
                toast('Error de conexión');
            }
        });

        // ===== Funciones del carrito AJAX =====
        let cartTimer = null;

        function showSaving(state) {
            const el = document.getElementById('saving-indicator');
            if (!el) return;
            el.classList.toggle('hidden', !state);
        }

        async function refreshCartFragment(html) {
            const root = document.getElementById('cart-root');
            if (!root) return;
            root.innerHTML = html;
        }

        async function submitCartAjax() {
            const form = document.getElementById('cart-form');
            if (!form) return;

            showSaving(true);

            try {
                const fd = new FormData(form);

                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: fd,
                });

                const data = await res.json();
                if (data.ok) {
                    setCartCount(data.cart_count ?? 0);
                    if (data.html) await refreshCartFragment(data.html);
                    toast(data.message || 'Actualizado ✅');
                } else {
                    toast('No se pudo actualizar');
                }
            } catch (err) {
                toast('Error de conexión');
            } finally {
                showSaving(false);
            }
        }

        // Estas 3 se usan dentro del fragmento del carrito
        function scheduleCartUpdate() {
            if (cartTimer) clearTimeout(cartTimer);
            showSaving(true);
            cartTimer = setTimeout(() => submitCartAjax(), 420);
        }

        function incQty(id) {
            const el = document.getElementById('qty-' + id);
            if (!el) return;
            el.value = Math.min(parseInt(el.value || '0', 10) + 1, 99);
            el.classList.add('scale-105');
            setTimeout(() => el.classList.remove('scale-105'), 120);
        }

        function decQty(id) {
            const el = document.getElementById('qty-' + id);
            if (!el) return;
            el.value = Math.max(parseInt(el.value || '0', 10) - 1, 0);
            el.classList.add('scale-105');
            setTimeout(() => el.classList.remove('scale-105'), 120);
        }

        function removeItem(id) {
            const el = document.getElementById('qty-' + id);
            if (!el) return;
            el.value = 0;
            scheduleCartUpdate();
        }

        function flyToCartFrom(imgEl) {
            const cartEl = document.getElementById('cart-link');
            if (!imgEl || !cartEl) return;

            const imgRect = imgEl.getBoundingClientRect();
            const cartRect = cartEl.getBoundingClientRect();

            // Burbuja (miniatura) para que se DISTINGA
            const bubble = document.createElement('div');
            const size = 88; // más visible
            bubble.style.position = 'fixed';
            bubble.style.width = size + 'px';
            bubble.style.height = size + 'px';
            bubble.style.left = (imgRect.left + imgRect.width / 2 - size / 2) + 'px';
            bubble.style.top = (imgRect.top + imgRect.height / 2 - size / 2) + 'px';
            bubble.style.borderRadius = '9999px';
            bubble.style.backgroundImage = `url('${imgEl.src}')`;
            bubble.style.backgroundSize = 'cover';
            bubble.style.backgroundPosition = 'center';
            bubble.style.zIndex = 999999;
            bubble.style.pointerEvents = 'none';

            // Estilo pro para que se note
            bubble.style.boxShadow = '0 18px 40px rgba(0,0,0,.22), 0 0 0 6px rgba(0,0,0,.08)';
            bubble.style.outline = '3px solid rgba(255,255,255,.85)';
            bubble.style.transform = 'translate3d(0,0,0) scale(1)';
            bubble.style.filter = 'saturate(1.1) contrast(1.05)';

            document.body.appendChild(bubble);

            const endX = (cartRect.left + cartRect.width / 2) - (imgRect.left + imgRect.width / 2);
            const endY = (cartRect.top + cartRect.height / 2) - (imgRect.top + imgRect.height / 2);

            // Usamos Web Animations API para movimiento suave (más notorio)
            bubble.animate([
                { transform: 'translate3d(0,0,0) scale(1)', opacity: 1 },
                { transform: `translate3d(${endX}px, ${endY}px, 0) scale(0.15)`, opacity: 0.15 }
            ], {
                duration: 850,
                easing: 'cubic-bezier(.16,1,.3,1)',
                fill: 'forwards'
            });

            setTimeout(() => {
                bubble.remove();
                popCartCount();
                shakeCart();
            }, 880);
        }

        function shakeCart() {
            const el = document.getElementById('cart-link');
            if (!el) return;
            el.classList.remove('cart-shake'); // reinicia si se llama seguido
            void el.offsetWidth; // force reflow
            el.classList.add('cart-shake');
            setTimeout(() => el.classList.remove('cart-shake'), 360);
        }

        // Vaciar carrito por AJAX
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('#btn-clear-cart');
            if (!btn) return;

            try {
                const res = await fetch("{{ route('cart.clear') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                });

                const data = await res.json();
                if (data.ok) {
                    setCartCount(0);
                    if (data.html) await refreshCartFragment(data.html);
                    toast(data.message || 'Carrito vaciado ✅');
                }
            } catch (err) {
                toast('Error de conexión');
            }
        });
    </script>

</body>

</html>