<x-layouts.app title="Pago aprobado | Todo Artesanal Tuxtla">
    <div class="max-w-xl mx-auto bg-white/85 backdrop-blur rounded-2xl border shadow p-8 text-center">
        <h1 class="text-3xl font-black text-[var(--ta-purple)]">¡Pago aprobado! ✅</h1>
        <p class="mt-3 text-slate-600">Gracias. En breve te contactamos por WhatsApp para confirmar detalles.</p>
        @php(session()->forget('cart')) {{-- vacía carrito en éxito --}}
        <a href="{{ route('home') }}"
            class="inline-block mt-6 bg-[var(--ta-pink)] hover:bg-[var(--ta-purple)] text-white font-black px-6 py-3 rounded-2xl transition">
            Volver a la tienda
        </a>
    </div>
</x-layouts.app>