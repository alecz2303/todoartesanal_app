<x-layouts.app :title="'Tienda | Todo Artesanal Tuxtla'">
    <div class="flex items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl md:text-4xl font-black tracking-tight">Piñatas y productos</h1>
            <p class="text-slate-600 mt-2">Elige tu favorita y agrégala al carrito.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($products as $product)
            <div
                class="bg-white/95 backdrop-blur rounded-2xl shadow-lg p-4 border-2 border-[var(--ta-pink-light)] hover:border-[var(--ta-purple)] transition">
                @if($product->cover_image_path)
                    <a href="{{ route('product.show', $product->slug) }}">
                        <img data-fly-img="p{{ $product->id }}" src="{{ asset('storage/' . $product->cover_image_path) }}"
                            class="w-full h-60 object-cover rounded-xl mb-4" alt="{{ $product->name }}">
                    </a>
                @endif

                <h2 class="text-xl font-semibold leading-tight">
                    <a href="{{ route('product.show', $product->slug) }}" class="hover:underline">
                        {{ $product->name }}
                    </a>
                </h2>

                @if($product->dimensions)
                    <p class="text-slate-500 text-sm mt-1">{{ $product->dimensions }}</p>
                @endif

                <div class="flex items-center justify-between mt-4">
                    <p class="text-2xl font-black">${{ $product->price_mx }}</p>

                    <form action="{{ route('cart.add', $product->id) }}" method="POST" data-ajax="add-to-cart"
                        data-fly-from="p{{ $product->id }}">
                        @csrf
                        <button
                            class="bg-[var(--ta-pink)] hover:bg-[var(--ta-purple)] text-white font-bold rounded-xl px-6 py-3 transition shadow-md">
                            Agregar
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-6 border text-slate-600">
                Aún no hay productos activos. Carga algunos en el panel /admin 🙂
            </div>
        @endforelse
    </div>
</x-layouts.app>