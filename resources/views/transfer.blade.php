<x-layouts.app title="Transferencia | Todo Artesanal Tuxtla">
    <div class="max-w-2xl mx-auto bg-white/85 backdrop-blur rounded-2xl border shadow p-8">
        <h1 class="text-3xl font-black text-[var(--ta-purple)]">Transferencia bancaria</h1>
        <p class="mt-2 text-slate-600">Haz tu transferencia y confirma tu pago para apartar tu pedido.</p>

        <div class="mt-6 rounded-2xl border p-5 bg-[var(--ta-bg)]">
            <p class="font-black">Datos bancarios</p>
            <p class="mt-2 text-sm"><b>Banco:</b> BBVA</p>
            <p class="text-sm"><b>Titular:</b> TODO ARTESANAL TUXTLA</p>
            <p class="text-sm"><b>CLABE:</b> <span class="font-mono">000000000000000000</span></p>
            <p class="text-sm"><b>Cuenta:</b> <span class="font-mono">0000000000</span></p>
            <p class="text-sm"><b>Referencia:</b> <span class="font-mono">Tu nombre + fecha</span></p>
        </div>

        <form class="mt-6" action="{{ route('transfer.confirm') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label class="text-sm font-semibold">Sube tu comprobante (opcional)</label>
            <input type="file" name="proof" class="mt-1 w-full rounded-xl border px-4 py-3 bg-white">

            <button
                class="mt-5 w-full bg-[var(--ta-pink)] hover:bg-[var(--ta-purple)] text-white font-black rounded-2xl px-6 py-4 transition shadow-lg">
                Confirmar transferencia
            </button>
        </form>
    </div>
</x-layouts.app>