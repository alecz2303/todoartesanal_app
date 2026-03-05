<x-layouts.app title="Transferencia bancaria | Todo Artesanal Tuxtla">
  <div class="max-w-xl mx-auto p-6">
    <div class="bg-white/85 backdrop-blur rounded-2xl border shadow p-8">
      <h1 class="text-2xl font-black text-[var(--ta-dark)]">Transferencia bancaria 🏦</h1>
      <p class="mt-2 text-slate-600">
        Orden <span class="font-black">#{{ $order->id }}</span> — Total
        <span class="font-black">${{ number_format($order->total_cents / 100, 2) }} MXN</span>
      </p>

      @if(session('success'))
        <div class="mt-4 p-3 rounded-xl border bg-emerald-50 text-emerald-800 text-sm">
          {{ session('success') }}
        </div>
      @endif

      @if(session('error'))
        <div class="mt-4 p-3 rounded-xl border bg-red-50 text-red-800 text-sm">
          {{ session('error') }}
        </div>
      @endif

      <div class="mt-6 p-4 rounded-2xl border bg-white">
        <div class="grid gap-2 text-sm">
          <div><span class="font-semibold">Banco:</span> {{ $bank['bank'] ?? '' }}</div>
          <div><span class="font-semibold">Titular:</span> {{ $bank['holder'] ?? '' }}</div>
          <div class="flex items-center gap-2">
            <span class="font-semibold">CLABE:</span>
            <span id="clabe" class="font-mono">{{ $bank['clabe'] ?? '' }}</span>
            <button type="button" class="text-xs px-2 py-1 rounded-lg border hover:bg-slate-50"
                    onclick="copyText(document.getElementById('clabe').innerText)">Copiar</button>
          </div>
          <div class="flex items-center gap-2">
            <span class="font-semibold">Cuenta:</span>
            <span id="account" class="font-mono">{{ $bank['account'] ?? '' }}</span>
            <button type="button" class="text-xs px-2 py-1 rounded-lg border hover:bg-slate-50"
                    onclick="copyText(document.getElementById('account').innerText)">Copiar</button>
          </div>
          <div>
            <span class="font-semibold">Referencia sugerida:</span>
            <span class="font-mono">{{ $reference }}</span>
          </div>
        </div>

        <p class="mt-3 text-xs text-slate-500">
          Importante: usa la referencia para identificar tu pago más rápido.
        </p>
      </div>

      <div class="mt-6 p-4 rounded-2xl border bg-white">
        <p class="font-black text-[var(--ta-purple)]">Comprobante (opcional)</p>
        <p class="text-sm text-slate-600 mt-1">Puedes subirlo aquí o enviarlo por WhatsApp.</p>

        <form class="mt-4 space-y-3" method="POST" action="{{ route('transfer.proof', $order) }}" enctype="multipart/form-data">
          @csrf
          <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" class="w-full rounded-xl border px-4 py-3 bg-white" required>
          @error('proof') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

          <button class="w-full rounded-2xl border bg-white hover:bg-white/70 px-6 py-3 font-black transition">
            Subir comprobante
          </button>
        </form>

        @if($order->transfer_proof_path)
          <p class="mt-3 text-sm text-emerald-700 font-semibold">Comprobante cargado ✅</p>
        @endif
      </div>

      <form class="mt-6" method="POST" action="{{ route('transfer.confirm', $order) }}">
        @csrf
        <button class="w-full rounded-2xl bg-[var(--ta-pink)] hover:bg-[var(--ta-purple)] text-white px-6 py-4 font-black transition shadow">
          Confirmar transferencia
        </button>
        <p class="mt-2 text-xs text-slate-500">
          Al confirmar, tu orden pasa a revisión.
        </p>
      </form>

      <a href="{{ route('home') }}" class="inline-block mt-6 underline text-sm">Volver a la tienda</a>
    </div>
  </div>

  <script>
    function copyText(text){
      navigator.clipboard?.writeText(text);
      if (window.toast) toast('Copiado ✅');
    }
  </script>
</x-layouts.app>