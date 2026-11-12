<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Pusat Daging Segar & Potongan Berkualitas') — DONS MEAT</title>
    <meta name="description" content="@yield('meta_description', 'DONS MEAT menyediakan aneka daging segar berkualitas: daging sapi, kambing, ayam, potongan khusus, dan aneka olahan daging dengan pemesanan online dan pengambilan di toko.')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-red-800 selection:text-white">
    
    <!-- Top Announcement Bar (Standard Beef Price) -->
    @php
        $globalSetting = $storeSetting ?? \App\Models\StoreSetting::current();
        $cartCount = $cartCount ?? app(\App\Services\CartService::class)->getCount();
    @endphp
    <div class="bg-red-950 text-red-100 text-xs py-2 px-4 border-b border-red-900/60">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-1 sm:gap-4 text-center sm:text-left">
            <div class="flex items-center space-x-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Toko Buka: {{ $globalSetting->opening_hours ? explode("\n", $globalSetting->opening_hours)[0] : '08.00 - 18.00 WITA' }}</span>
            </div>
            <div class="flex items-center space-x-2 font-medium">
                <span class="text-red-300">Harga Acuan Standar Daging:</span>
                <span class="font-bold text-amber-300">Rp {{ number_format($globalSetting->standard_beef_price_per_kg, 0, ',', '.') }}/kg</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                @if($globalSetting->logo_path && file_exists(public_path('storage/' . $globalSetting->logo_path)))
                    <img src="{{ asset('storage/' . $globalSetting->logo_path) }}" 
                         alt="{{ $globalSetting->business_name }}" 
                         class="h-11 w-auto max-w-[120px] object-contain rounded-xl shadow-xs group-hover:scale-105 transition transform">
                @else
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-red-700 to-red-900 text-white flex items-center justify-center font-extrabold text-2xl shadow-md group-hover:scale-105 transition transform">
                        D
                    </div>
                @endif
                <div>
                    <span class="font-extrabold text-xl sm:text-2xl tracking-wider text-slate-900 block leading-tight">{{ $globalSetting->business_name }}</span>
                    <span class="text-[11px] font-semibold text-red-700 tracking-wide block uppercase">Fresh Meat & Quality Cuts</span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center space-x-8 text-sm font-semibold">
                <a href="{{ route('home') }}" 
                   class="transition {{ request()->routeIs('home') ? 'text-red-700' : 'text-slate-600 hover:text-red-700' }}">
                    Beranda
                </a>
                <a href="{{ route('catalog.index') }}" 
                   class="transition {{ request()->routeIs('catalog.*') ? 'text-red-700' : 'text-slate-600 hover:text-red-700' }}">
                    Katalog Daging
                </a>
                <a href="{{ route('store.profile') }}" 
                   class="transition {{ request()->routeIs('store.profile') ? 'text-red-700' : 'text-slate-600 hover:text-red-700' }}">
                    Tentang Kami
                </a>
            </nav>

            <!-- Actions (Cart & Panel) -->
            <div class="flex items-center space-x-3">
                <!-- Cart Button -->
                <a href="{{ route('cart.index') }}" 
                   id="cartNavButton"
                   class="relative inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-semibold border border-slate-200/80 transition group">
                    <svg class="w-5 h-5 text-slate-700 group-hover:text-red-700 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="hidden sm:inline-block ml-2">Keranjang</span>
                    <span id="cartCountBadge" 
                          class="ml-1.5 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold leading-none rounded-full transition {{ ($cartCount ?? 0) > 0 ? 'bg-red-700 text-white shadow-xs' : 'bg-slate-200 text-slate-600' }}">{{ $cartCount ?? 0 }}</span>
                </a>

                <!-- Internal Login Link -->
                <a href="{{ route('admin.login') }}" 
                   class="hidden lg:inline-flex items-center px-3 py-2 text-xs font-medium text-slate-400 hover:text-slate-700 transition">
                    Panel
                </a>

                <!-- Mobile menu toggle button -->
                <button type="button" 
                        onclick="document.getElementById('mobileNavMenu').classList.toggle('hidden')"
                        class="md:hidden p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Nav Menu (dropdown) -->
        <div id="mobileNavMenu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-2 text-sm font-semibold">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('home') ? 'bg-red-50 text-red-700' : 'text-slate-700 hover:bg-slate-50' }}">
                Beranda
            </a>
            <a href="{{ route('catalog.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('catalog.*') ? 'bg-red-50 text-red-700' : 'text-slate-700 hover:bg-slate-50' }}">
                Katalog Daging
            </a>
            <a href="{{ route('cart.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('cart.*') ? 'bg-red-50 text-red-700' : 'text-slate-700 hover:bg-slate-50' }}">
                Keranjang Belanja ({{ $cartCount ?? 0 }})
            </a>
            <a href="{{ route('store.profile') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('store.profile') ? 'bg-red-50 text-red-700' : 'text-slate-700 hover:bg-slate-50' }}">
                Tentang Kami
            </a>
            <a href="{{ route('admin.login') }}" class="block px-3 py-2 rounded-lg text-slate-400 hover:bg-slate-50 text-xs">
                Login Panel Internal
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center space-x-3">
                        @if($globalSetting->logo_path && file_exists(public_path('storage/' . $globalSetting->logo_path)))
                            <img src="{{ asset('storage/' . $globalSetting->logo_path) }}" 
                                 alt="{{ $globalSetting->business_name }}" 
                                 class="h-10 w-auto max-w-[110px] object-contain rounded-xl bg-white p-1">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600 to-red-800 text-white flex items-center justify-center font-extrabold text-xl">
                                D
                            </div>
                        @endif
                        <span class="font-extrabold text-xl text-white tracking-wider">{{ $globalSetting->business_name }}</span>
                    </div>
                    <p class="text-sm text-slate-400 max-w-sm leading-relaxed">
                        {{ $globalSetting->business_description ?? 'Pusat penyedia aneka daging segar (sapi, kambing, ayam), potongan khusus, dan aneka olahan berkualitas tinggi.' }}
                    </p>
                    <div class="pt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-red-950/80 text-amber-300 border border-red-900/50">
                            Harga Acuan Daging: Rp {{ number_format($globalSetting->standard_beef_price_per_kg, 0, ',', '.') }}/kg
                        </span>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider text-slate-200 mb-3">Navigasi</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition">Beranda</a></li>
                        <li><a href="{{ route('catalog.index') }}" class="hover:text-white transition">Katalog Produk</a></li>
                        <li><a href="{{ route('store.profile') }}" class="hover:text-white transition">Informasi Usaha</a></li>
                        <li><a href="{{ route('admin.login') }}" class="hover:text-white transition">Panel Internal</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider text-slate-200 mb-3">Kontak & Lokasi</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li>📍 {{ $globalSetting->address ?? 'Makassar, Sulawesi Selatan' }}</li>
                        <li>📞 {{ $globalSetting->phone ?? '08123456789' }}</li>
                        @if($globalSetting->email)
                            <li>✉️ {{ $globalSetting->email }}</li>
                        @endif
                    </ul>
                </div>

            </div>

            <div class="mt-12 pt-8 border-t border-slate-800 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} {{ $globalSetting->business_name }}. Seluruh hak cipta dilindungi.
            </div>
        </div>
    </footer>

</body>
</html>
