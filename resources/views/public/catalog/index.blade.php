@extends('layouts.public')

@section('title', 'Katalog Daging Pilihan')
@section('meta_description', 'Jelajahi aneka pilihan daging segar berkualitas DONS MEAT: sapi, kambing, ayam, potongan khusus, dan tulang dengan berbagai opsi berat.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-8">
    
    <!-- Title & Search Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Katalog Daging Segar</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Pilih aneka daging segar berkualitas sesuai kebutuhan potongan dan berat Anda.
            </p>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('catalog.index') }}" class="flex items-center gap-2 max-w-md w-full">
            @if(request('kategori'))
                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
            @endif
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            <div class="relative flex-1">
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}"
                       placeholder="Cari aneka daging, potongan, atau SKU..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 bg-white shadow-xs">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button type="submit" class="px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-sm font-semibold transition">
                Cari
            </button>
        </form>
    </div>

    <!-- Category Filter Pills & Sort Selector -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <!-- Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
            <a href="{{ route('catalog.index', array_merge(request()->except('kategori', 'page'))) }}" 
               class="px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition {{ !request('kategori') ? 'bg-red-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Semua Kategori
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('catalog.index', array_merge(request()->except('page'), ['kategori' => $cat->slug])) }}" 
                   class="px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition {{ request('kategori') === $cat->slug ? 'bg-red-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    {{ $cat->name }} ({{ $cat->products_count }})
                </a>
            @endforeach
        </div>

        <!-- Sort selector -->
        <div class="flex items-center space-x-2 text-xs">
            <span class="text-slate-400 font-medium">Urutkan:</span>
            <form method="GET" action="{{ route('catalog.index') }}" id="sortForm">
                @if(request('kategori'))
                    <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                @endif
                @if(request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif
                <select name="sort" 
                        onchange="document.getElementById('sortForm').submit()"
                        class="px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-slate-700 focus:ring-1 focus:ring-red-600">
                    <option value="terbaru" {{ request('sort') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                    <option value="harga_terendah" {{ request('sort') === 'harga_terendah' ? 'selected' : '' }}>Harga Terendah</option>
                    <option value="harga_tertinggi" {{ request('sort') === 'harga_tertinggi' ? 'selected' : '' }}>Harga Tertinggi</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Active Filters Alert (if filtered) -->
    @if(request('q') || request('kategori'))
        <div class="flex items-center justify-between text-xs text-slate-600 bg-slate-100 px-4 py-2 rounded-xl">
            <span>
                Menampilkan hasil untuk:
                @if(request('q')) <strong class="text-slate-900">"{{ request('q') }}"</strong> @endif
                @if(request('kategori')) Kategori <strong class="text-slate-900">{{ $selectedCategory?->name ?? request('kategori') }}</strong> @endif
            </span>
            <a href="{{ route('catalog.index') }}" class="font-bold text-red-700 hover:underline">
                Reset Filter
            </a>
        </div>
    @endif

    <!-- Product Grid -->
    @if($products->isEmpty())
        <div class="text-center py-16 bg-white rounded-3xl border border-slate-200 p-8">
            <span class="text-5xl block mb-3">🥩</span>
            <h3 class="text-lg font-bold text-slate-900">Tidak ada produk yang cocok</h3>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                Silakan coba kata kunci pencarian lain atau pilih kategori yang berbeda.
            </p>
            <div class="mt-4">
                <a href="{{ route('catalog.index') }}" class="inline-block px-4 py-2 rounded-xl text-xs font-semibold text-red-700 bg-red-50 border border-red-200">
                    Kembali ke Seluruh Produk
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between">
                    <!-- Image with badges -->
                    <div class="aspect-4/3 bg-slate-100 relative overflow-hidden group">
                        @if($product->image_path)
                            <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 bg-gradient-to-br from-slate-100 to-slate-200">
                                <span class="text-3xl mb-1">🥩</span>
                                <span class="text-[11px] font-semibold text-slate-400">DONS MEAT</span>
                            </div>
                        @endif

                        <div class="absolute top-2.5 left-2.5">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-white/95 text-red-900 backdrop-blur-xs shadow-xs">
                                {{ $product->category?->name }}
                            </span>
                        </div>

                        <div class="absolute top-2.5 right-2.5">
                            @if($product->available_stock_gram > 0)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-600 text-white shadow-xs">
                                    Tersedia
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-700 text-white shadow-xs">
                                    Habis
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 line-clamp-1">
                                <a href="{{ route('catalog.show', $product->slug) }}" class="hover:text-red-700 transition">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $product->description }}</p>

                            <div class="mt-3">
                                <span class="text-[11px] text-slate-400 font-medium">Harga / kg</span>
                                <div class="text-base font-extrabold text-red-800">
                                    Rp {{ number_format($product->price_per_kg, 0, ',', '.') }}
                                </div>
                            </div>

                            <!-- Weight preview -->
                            <div class="mt-2.5 flex flex-wrap gap-1">
                                @forelse($product->activeWeightOptions as $opt)
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-semibold text-slate-600 border border-slate-200">
                                        {{ $opt->label }}
                                    </span>
                                @empty
                                    <span class="text-[10px] text-slate-400 italic">Pilihan berat menyusul</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <a href="{{ route('catalog.show', $product->slug) }}" 
                               class="block w-full py-2 px-3 rounded-xl text-center text-xs font-bold text-white bg-red-700 hover:bg-red-800 transition">
                                Lihat Detail & Berat
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-6">
            {{ $products->links() }}
        </div>
    @endif

</div>
@endsection
