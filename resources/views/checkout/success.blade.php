<x-layouts.app title="Pago | Todo Artesanal Tuxtla">
  <div class="max-w-xl mx-auto p-6">
    <div class="bg-white/85 backdrop-blur rounded-2xl border shadow p-8 text-center">
      <h1 class="text-3xl font-black text-[var(--ta-purple)]">¡Listo! ✅</h1>

      <p class="mt-3 text-slate-600">
        Tu orden <span class="font-black">#{{ $order->id }}</span> fue creada.
      </p>

      <div class="mt-4 rounded-2xl border bg-white p-4 text-left">
        <p class="text-sm text-slate-600">Estado actual:</p>
        <p class="font-black">
          {{ is_object($order->status) ? $order->status->value : $order->status }}
        </p>

        @if(!empty($order->mp_status))
          <p class="mt-2 text-sm text-slate-600">Mercado Pago:</p>
          <p class="font-semibold">{{ $order->mp_status }}</p>
        @endif
      </div>

      <p class="mt-4 text-sm text-slate-600">
        El estado final se confirma automáticamente con el webhook de Mercado Pago.
      </p>

      <a href="{{ route('home') }}"
         class="inline-block mt-6 bg-[var(--ta-pink)] hover:bg-[var(--ta-purple)] text-white font-black px-6 py-3 rounded-2xl transition">
        Volver a la tienda
      </a>
    </div>
  </div>
</x-layouts.app>