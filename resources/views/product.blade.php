<x-layouts.app :title="$product->name . ' | Todo Artesanal Tuxtla'">

    <div class="max-w-4xl mx-auto">

        <a href="{{ route('home') }}" class="text-sm text-slate-500 hover:underline">
            ← Volver
        </a>

        <div class="grid md:grid-cols-2 gap-10 mt-6">

            @if($product->cover_image_path)
                <div class="rounded-2xl shadow overflow-hidden group bg-white">
                    <img id="product-image" src="{{ asset('storage/' . $product->cover_image_path) }}"
                        class="w-full transition-transform duration-300 ease-out group-hover:scale-105"
                        alt="{{ $product->name }}">
                </div>
            @endif

            <div>
                <h1 class="text-3xl md:text-4xl font-black mb-3">
                    {{ $product->name }}
                </h1>

                @if($product->description)
                    <p class="text-slate-600 mb-4">
                        {{ $product->description }}
                    </p>
                @endif

                @if($product->dimensions)
                    <p class="text-sm text-slate-500 mb-4">
                        Medidas: {{ $product->dimensions }}
                    </p>
                @endif

                <p class="text-3xl font-black mb-6">
                    ${{ $product->price_mx }}
                </p>

                <form action="{{ route('cart.add', $product->id) }}" method="POST" data-ajax="add-to-cart">
                    @csrf
                    <button
                        class="bg-[var(--ta-pink)] hover:bg-[var(--ta-purple)] text-white font-bold rounded-xl px-6 py-3 transition shadow-md"
                        data-fly="product">
                        Agregar al carrito
                    </button>
                </form>
            </div>

        </div>
    </div>

</x-layouts.app>