@extends('layouts.public')

@section('title', $product->name)
@section('meta_description', Str::limit(strip_tags($product->description), 150))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-12">
    
    <!-- Breadcrumb -->
    <nav class="flex text-xs font-semibold text-slate-500 space-x-2">
        <a href="{{ route('home') }}" class="hover:text-red-700">Beranda</a>
        <span>/</span>
        <a href="{{ route('catalog.index') }}" class="hover:text-red-700">Katalog</a>
        @if($product->category)
            <span>/</span>
            <a href="{{ route('catalog.index', ['kategori' => $product->category->slug]) }}" class="hover:text-red-700">
                {{ $product->category->name }}
            </a>
        @endif
        <span>/</span>
        <span class="text-slate-800">{{ $product->name }}</span>
    </nav>

    <!-- Main Detail Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
        
        <!-- Left: Product Image -->
        <div class="lg:col-span-6 space-y-4">
            <div class="aspect-4/3 sm:aspect-square bg-slate-100 rounded-3xl overflow-hidden border border-slate-200 relative shadow-xs">
                @if($product->image_path)
                    <img src="{{ asset('storage/'.$product->image_path) }}" 
                         alt="{{ $product->name }}" 
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 bg-gradient-to-br from-slate-100 to-slate-200">
                        <span class="text-6xl mb-2">🥩</span>
                        <span class="text-sm font-semibold text-slate-400">DONS MEAT Fresh Meat</span>
                    </div>
                @endif

                <div class="absolute top-4 left-4">
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-white/95 text-red-900 shadow-sm backdrop-blur-xs">
                        {{ $product->category?->name }}
                    </span>
                </div>

                <div class="absolute top-4 right-4">
                    @if($product->available_stock_gram > 0)
                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-emerald-600 text-white shadow-sm">
                            Tersedia: {{ number_format($product->available_stock_gram / 1000, 1) }} kg
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-800 text-white shadow-sm">
                            Stok Habis
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Information & Weight Selection Form -->
        <div class="lg:col-span-6 space-y-6">
            <div>
                <div class="flex items-center space-x-2 text-xs font-semibold text-slate-400 mb-1">
                    <span>SKU: {{ $product->sku }}</span>
                    <span>•</span>
                    <span>Satuan Standar: Gram</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    {{ $product->name }}
                </h1>
                
                <div class="mt-4 p-4 rounded-2xl bg-red-50/70 border border-red-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-red-700 block">Harga Dasar</span>
                        <span class="text-2xl sm:text-3xl font-extrabold text-red-900">
                            Rp {{ number_format($product->price_per_kg, 0, ',', '.') }}
                        </span>
                        <span class="text-xs font-semibold text-red-700"> / kg</span>
                    </div>
                    <div class="text-right text-xs text-slate-500">
                        <span>Pilihan berat dihitung</span>
                        <span class="block font-semibold text-slate-700">sesuai berat paket</span>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="prose prose-sm text-slate-600 leading-relaxed border-t border-b border-slate-100 py-4">
                <p>{{ $product->description ?? 'Daging sapi segar dengan standar higienis dan potongan presisi untuk menjaga kualitas rasa dan tekstur.' }}</p>
            </div>

            <!-- Weight Selection & Purchase Card Form -->
            <form method="POST" action="{{ route('cart.store') }}" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6" id="weightSelectionContainer">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    Pilih Pilihan Berat
                </h3>

                @if($product->activeWeightOptions->isEmpty())
                    <p class="text-xs text-slate-500 italic">
                        Belum ada opsi berat yang diaktifkan untuk produk ini.
                    </p>
                @else
                    @php
                        $firstAvailableOption = $product->activeWeightOptions->first(fn($opt) => $product->available_stock_gram >= $opt->weight_gram) ?? $product->activeWeightOptions->first();
                        $initialPrice = $firstAvailableOption ? round(($product->price_per_kg * $firstAvailableOption->weight_gram) / 1000) : 0;
                    @endphp

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="weightOptionsGrid">
                        @foreach($product->activeWeightOptions as $index => $option)
                            @php
                                $packagePrice = round(($product->price_per_kg * $option->weight_gram) / 1000);
                                $isAvailable = $product->available_stock_gram >= $option->weight_gram;
                                $isSelected = $firstAvailableOption && $option->id === $firstAvailableOption->id;
                            @endphp
                            <label class="relative flex flex-col p-3 rounded-xl border text-center cursor-pointer transition select-none {{ $isAvailable ? 'border-slate-200 bg-white hover:border-red-400 has-[:checked]:border-red-600 has-[:checked]:ring-2 has-[:checked]:ring-red-600 has-[:checked]:bg-red-50/40' : 'border-slate-100 bg-slate-100/60 opacity-60 cursor-not-allowed' }}">
                                <input type="radio" 
                                       name="weight_option_id" 
                                       value="{{ $option->id }}"
                                       data-gram="{{ $option->weight_gram }}"
                                       data-label="{{ $option->label }}"
                                       data-price="{{ $packagePrice }}"
                                       data-available="{{ $isAvailable ? '1' : '0' }}"
                                       {{ $isSelected ? 'checked' : '' }}
                                       {{ ! $isAvailable ? 'disabled' : '' }}
                                       onchange="updateSubtotal()"
                                       class="sr-only">
                                
                                <span class="font-extrabold text-sm text-slate-900 group-hover:text-red-700">
                                    {{ $option->label }}
                                </span>
                                
                                <span class="text-xs font-bold text-red-800 mt-1">
                                    Rp {{ number_format($packagePrice, 0, ',', '.') }}
                                </span>

                                @if(! $isAvailable)
                                    <span class="mt-1 text-[10px] text-rose-600 font-semibold">Stok kurang</span>
                                @endif
                            </label>
                        @endforeach
                    </div>

                    <!-- Quantity & Live Subtotal Display -->
                    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center space-x-3 w-full sm:w-auto">
                            <span class="text-xs font-semibold text-slate-600">Jumlah:</span>
                            <div class="flex items-center border border-slate-300 rounded-xl overflow-hidden bg-slate-50 shadow-xs">
                                <button type="button" 
                                        id="btnDecreaseQty"
                                        onclick="adjustQty(-1)" 
                                        aria-label="Kurangi jumlah"
                                        class="w-9 h-9 flex items-center justify-center text-slate-700 hover:bg-slate-200 active:bg-slate-300 font-bold text-base transition select-none cursor-pointer">
                                    -
                                </button>
                                <input type="number" 
                                       name="quantity"
                                       id="itemQty" 
                                       value="1" 
                                       min="1" 
                                       max="50" 
                                       oninput="updateSubtotal()"
                                       onchange="updateSubtotal()"
                                       class="w-12 text-center text-sm font-bold bg-white border-y-0 border-x border-slate-200 focus:outline-none focus:ring-0 py-1.5 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" 
                                        id="btnIncreaseQty"
                                        onclick="adjustQty(1)" 
                                        aria-label="Tambah jumlah"
                                        class="w-9 h-9 flex items-center justify-center text-slate-700 hover:bg-slate-200 active:bg-slate-300 font-bold text-base transition select-none cursor-pointer">
                                    +
                                </button>
                            </div>
                        </div>

                        <div class="text-right w-full sm:w-auto">
                            <span class="text-xs text-slate-400 font-medium block">Total Pembelian</span>
                            <span id="calculatedSubtotal" class="text-xl sm:text-2xl font-extrabold text-red-800">
                                Rp {{ number_format($initialPrice, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2">
                        @if($product->available_stock_gram > 0)
                            <button type="submit" 
                                    id="addToCartBtn"
                                    class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-red-700 to-red-800 hover:from-red-800 hover:to-red-900 text-white font-extrabold text-sm sm:text-base shadow-md transition flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Tambah ke Keranjang</span>
                            </button>
                        @else
                            <button type="button" disabled class="w-full py-3.5 px-6 rounded-xl bg-slate-200 text-slate-400 font-bold text-sm cursor-not-allowed">
                                Stok Habis Saat Ini
                            </button>
                        @endif
                    </div>
                @endif
            </form>

            <!-- Notes on Weight & Hygiene -->
            <div class="rounded-2xl bg-slate-100 p-4 text-xs text-slate-600 space-y-1">
                <p class="font-bold text-slate-800">ℹ️ Kebijakan Pemesanan DONS MEAT:</p>
                <p>• Daging dipotong dan dikemas sesuai pilihan berat yang dipilih.</p>
                <p>• Stok online dan stok toko tersinkronisasi otomatis.</p>
                <p>• Pengambilan di toko maupun pengiriman kurir dapat dipilih saat checkout.</p>
            </div>
        </div>

    </div>

    <!-- Related Products -->
    @if($relatedProducts->isNotEmpty())
        <div class="border-t border-slate-200 pt-12">
            <h3 class="text-xl font-bold text-slate-900 mb-6">Produk Terkait</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $rel)
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="aspect-4/3 bg-slate-100 rounded-xl overflow-hidden mb-3">
                                @if($rel->image_path)
                                    <img src="{{ asset('storage/'.$rel->image_path) }}" alt="{{ $rel->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">🥩</div>
                                @endif
                            </div>
                            <h4 class="font-bold text-sm text-slate-900 line-clamp-1">{{ $rel->name }}</h4>
                            <p class="text-xs font-extrabold text-red-800 mt-1">
                                Rp {{ number_format($rel->price_per_kg, 0, ',', '.') }} <span class="text-[10px] text-slate-400 font-normal">/ kg</span>
                            </p>
                        </div>
                        <a href="{{ route('catalog.show', $rel->slug) }}" class="mt-3 block text-center py-1.5 px-3 rounded-lg text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 transition">
                            Lihat Produk
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<!-- Client-side weight & subtotal script -->
<script>
    function updateSubtotal() {
        let checkedRadio = document.querySelector('input[name="weight_option_id"]:checked');
        const qtyInput = document.getElementById('itemQty');
        const subtotalEl = document.getElementById('calculatedSubtotal');
        
        if (!qtyInput || !subtotalEl) return;

        // Auto-fallback: if no option is checked, select first available non-disabled option
        if (!checkedRadio) {
            const firstAvailable = document.querySelector('input[name="weight_option_id"]:not(:disabled)');
            if (firstAvailable) {
                firstAvailable.checked = true;
                checkedRadio = firstAvailable;
            }
        }

        let qty = parseInt(qtyInput.value) || 1;
        if (qty < 1) qty = 1;
        if (qty > 50) qty = 50;
        qtyInput.value = qty;

        if (!checkedRadio) {
            subtotalEl.innerText = 'Rp 0';
            return;
        }

        const packagePrice = parseFloat(checkedRadio.dataset.price) || 0;
        const total = packagePrice * qty;
        subtotalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    function adjustQty(delta) {
        const qtyInput = document.getElementById('itemQty');
        if (!qtyInput) return;

        let val = parseInt(qtyInput.value) || 1;
        val += delta;
        if (val < 1) val = 1;
        if (val > 50) val = 50;
        qtyInput.value = val;
        updateSubtotal();
    }

    // Expose functions globally to window
    window.updateSubtotal = updateSubtotal;
    window.adjustQty = adjustQty;

    function initProductDetailControls() {
        const qtyInput = document.getElementById('itemQty');

        if (qtyInput && !qtyInput.dataset.ready) {
            qtyInput.addEventListener('input', updateSubtotal);
            qtyInput.addEventListener('change', updateSubtotal);
            qtyInput.dataset.ready = 'true';
        }

        document.querySelectorAll('input[name="weight_option_id"]').forEach(function(radio) {
            if (!radio.dataset.ready) {
                radio.addEventListener('change', updateSubtotal);
                radio.dataset.ready = 'true';
            }
        });

        updateSubtotal();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductDetailControls);
    } else {
        initProductDetailControls();
    }
</script>
@endsection
