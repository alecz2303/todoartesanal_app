<x-layouts.app title="Transferencia confirmada | Todo Artesanal Tuxtla">
  <div class="max-w-xl mx-auto p-6">
    <div class="bg-white/85 backdrop-blur rounded-2xl border shadow p-8 text-center">
      <h1 class="text-3xl font-black text-[var(--ta-purple)]">¡Recibido! ✅</h1>

      <p class="mt-3 text-slate-600">
        Tu transferencia para la orden <span class="font-black">#{{ $order->id }}</span> quedó confirmada.
      </p>

      @if(session('success'))
        <p class="mt-3 text-sm text-emerald-700 font-semibold">{{ session('success') }}</p>
      @endif

      <a href="{{ route('home') }}"
         class="inline-block mt-6 bg-[var(--ta-pink)] hover:bg-[var(--ta-purple)] text-white font-black px-6 py-3 rounded-2xl transition">
        Volver a la tienda
      </a>
    </div>
  </div>
</x-layouts.app>