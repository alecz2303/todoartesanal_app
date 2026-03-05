<x-layouts.app title="Pago no completado | Todo Artesanal Tuxtla">
  <div class="max-w-xl mx-auto p-6">
    <div class="bg-white/85 backdrop-blur rounded-2xl border shadow p-8 text-center">
      <h1 class="text-3xl font-black text-red-600">Pago no completado 😕</h1>

      <p class="mt-3 text-slate-600">
        La orden <span class="font-black">#{{ $order->id }}</span> sigue registrada.
      </p>

      <p class="mt-2 text-sm text-slate-500">
        Si realizaste el pago, espera unos momentos: el webhook actualizará el estado automáticamente.
      </p>

      <a href="{{ route('home') }}"
         class="inline-block mt-6 bg-[var(--ta-pink)] hover:bg-[var(--ta-purple)] text-white font-black px-6 py-3 rounded-2xl transition">
        Volver a la tienda
      </a>
    </div>
  </div>
</x-layouts.app>