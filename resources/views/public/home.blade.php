@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
<div class="space-y-16 sm:space-y-24 pb-16">

    <!-- Hero Section -->
    <section class="relative bg-slate-950 text-white overflow-hidden py-16 sm:py-24">
        <!-- Background Photo Asset -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/hero-meat.jpg') }}" 
                 alt="Background Daging Sapi Segar DONS MEAT" 
                 class="w-full h-full object-cover object-center filter brightness-75 contrast-110">
            <!-- Dark culinary overlay to ensure contrast and readability -->
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/60 to-black/80"></div>
        </div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left text content -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold tracking-wide uppercase bg-red-700/80 text-red-100 border border-red-600/60 shadow-md backdrop-blur-sm">
                        Daging Sapi Segar Pilihan Terbaik
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white drop-shadow-md">
                        Kualitas Terbaik untuk Setiap Potongan Daging Sapi Anda
                    </h1>

                    <p class="text-base sm:text-lg text-slate-100/90 max-w-xl mx-auto lg:mx-0 font-normal leading-relaxed drop-shadow-sm">
                        {{ $setting->business_description ?? 'DONS MEAT menyediakan daging sapi segar dengan pilihan potongan utuh, potongan khusus, dan tulang. Siap untuk pengambilan di toko atau pengiriman langsung.' }}
                    </p>

                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="{{ route('catalog.index') }}" 
                           id="heroCatalogCta"
                           class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-extrabold text-base shadow-xl hover:shadow-red-600/40 transition transform hover:-translate-y-0.5 text-center">
                            Lihat Katalog Lengkap
                        </a>
                        <a href="{{ route('store.profile') }}" 
                           class="w-full sm:w-auto px-6 py-4 rounded-xl bg-white/15 hover:bg-white/25 text-white font-semibold text-base border border-white/30 backdrop-blur-md transition text-center">
                            Info & Jam Layanan
                        </a>
                    </div>
                </div>

                <!-- Right Price Feature Card -->
                <div class="lg:col-span-5">
                    <div class="bg-black/40 backdrop-blur-xl rounded-3xl p-6 sm:p-8 border border-white/20 shadow-2xl space-y-6 text-white text-center">
                        <div class="space-y-1">
                            <span class="inline-block text-xs uppercase tracking-widest font-extrabold text-red-300">
                                Acuan Resmi Usaha
                            </span>
                            <h2 class="text-xl font-bold">Harga Standar Daging Sapi</h2>
                        </div>

                        <div class="py-4 bg-black/30 rounded-2xl border border-white/10">
                            <div class="text-xs text-red-200 font-semibold mb-1">Per Kilogram (1.000 g)</div>
                            <div class="text-4xl sm:text-5xl font-black text-amber-300 tracking-tight font-mono">
                                Rp {{ number_format($setting->standard_beef_price_per_kg, 0, ',', '.') }}
                            </div>
                            <div class="text-[11px] text-slate-300 mt-1">Dapat diperbarui sewaktu-waktu oleh manajemen</div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-left text-xs text-slate-200">
                            <div class="p-3 rounded-xl bg-white/10 border border-white/10">
                                <span class="font-bold text-white block">🛒 Pilihan Berat</span>
                                <span class="text-slate-300 text-[11px]">250 g, 500 g, 1 kg</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/10 border border-white/10">
                                <span class="font-bold text-white block">🚚 Pengiriman</span>
                                <span class="text-slate-300 text-[11px]">Ambil di toko / Delivery</span>
                            </div>
                        </div>

                        <a href="{{ route('catalog.index') }}" class="block text-center text-xs font-bold text-amber-300 hover:text-amber-200 underline">
                            Pilih produk sekarang &rarr;
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-red-700">Pilihan Produk</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Kategori Daging Sapi</h2>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-sm font-bold text-red-700 hover:text-red-800">
                Semua Kategori &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($categories as $category)
                <a href="{{ route('catalog.index', ['kategori' => $category->slug]) }}" 
                   class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-red-200 transition">
                    <div class="w-12 h-12 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-xl mb-4 group-hover:scale-110 transition">
                        🥩
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-red-700 transition">{{ $category->name }}</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-2 line-clamp-2 leading-relaxed">
                        {{ $category->description ?? 'Daging sapi segar pilihan dengan kualitas terjamin.' }}
                    </p>
                    <div class="mt-4 flex items-center justify-between text-xs font-semibold text-slate-400">
                        <span>{{ $category->products_count }} Produk</span>
                        <span class="text-red-700 group-hover:translate-x-1 transition">Lihat &rarr;</span>
                    </div>
                </a>
            @empty
                <div class="col-span-3 text-center py-8 text-slate-400">
                    Kategori belum tersedia.
                </div>
            @endforelse
        </div>
    </section>

    <!-- Featured Products Section -->
    @if($featuredProducts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-red-700">Favorit Pelanggan</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Produk Unggulan</h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-sm font-bold text-red-700 hover:text-red-800">
                    Buka Seluruh Katalog &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featuredProducts as $product)
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-lg transition flex flex-col">
                        <!-- Product Image -->
                        <div class="aspect-4/3 bg-slate-100 relative overflow-hidden">
                            @if($product->image_path)
                                <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 bg-gradient-to-br from-slate-100 to-slate-200">
                                    <span class="text-4xl mb-1">🥩</span>
                                    <span class="text-xs font-medium text-slate-400">DONS MEAT</span>
                                </div>
                            @endif

                            <div class="absolute top-3 left-3">
                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white/90 text-red-800 backdrop-blur-xs shadow-xs">
                                    {{ $product->category?->name }}
                                </span>
                            </div>

                            <div class="absolute top-3 right-3">
                                @if($product->available_stock_gram > 0)
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-600 text-white shadow-xs">
                                        Tersedia
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-700 text-white shadow-xs">
                                        Habis
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Product Content -->
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-base text-slate-900 line-clamp-1">{{ $product->name }}</h3>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $product->description ?? 'Daging sapi segar dengan standar kualitas tinggi.' }}</p>

                                <div class="mt-3">
                                    <span class="text-xs text-slate-400 font-medium">Harga / kg</span>
                                    <div class="text-lg font-extrabold text-red-800">
                                        Rp {{ number_format($product->price_per_kg, 0, ',', '.') }}
                                    </div>
                                </div>

                                <!-- Weight Options preview -->
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach($product->activeWeightOptions as $opt)
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[11px] font-semibold text-slate-600 border border-slate-200">
                                            {{ $opt->label }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-5 pt-4 border-t border-slate-100">
                                <a href="{{ route('catalog.show', $product->slug) }}" 
                                   class="block w-full py-2.5 px-4 rounded-xl text-center text-xs font-bold text-white bg-red-700 hover:bg-red-800 transition">
                                    Pilih Berat & Beli
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Store Info & Process Banner -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-slate-900 rounded-3xl p-8 sm:p-12 text-white">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-red-900/60 text-red-300 flex items-center justify-center font-bold text-lg mx-auto md:mx-0">
                        1
                    </div>
                    <h3 class="font-bold text-lg text-white">Pilih Berat Sesuai Kebutuhan</h3>
                    <p class="text-xs sm:text-sm text-slate-400">
                        Tersedia opsi berat 250 g, 500 g, hingga 1 kg yang sudah disesuaikan agar mudah dimasak.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-red-900/60 text-red-300 flex items-center justify-center font-bold text-lg mx-auto md:mx-0">
                        2
                    </div>
                    <h3 class="font-bold text-lg text-white">Pilih Ambil Sendiri atau Antar</h3>
                    <p class="text-xs sm:text-sm text-slate-400">
                        Anda dapat mengambil langsung di toko atau memilih pengiriman ke alamat tujuan.
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-red-900/60 text-red-300 flex items-center justify-center font-bold text-lg mx-auto md:mx-0">
                        3
                    </div>
                    <h3 class="font-bold text-lg text-white">Tanpa Ribet Mendaftar Akun</h3>
                    <p class="text-xs sm:text-sm text-slate-400">
                        Langsung checkout sebagai tamu, bayar dengan aman, dan pantau status pesanan secara real-time.
                    </p>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
