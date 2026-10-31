<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — DONS MEAT Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans text-slate-800" x-data="{ mobileMenuOpen: false }">
    <div class="min-h-full flex flex-col lg:flex-row">
        
        <!-- Mobile Header Bar -->
        <header class="lg:hidden flex items-center justify-between px-4 py-3 bg-red-950 text-white border-b border-red-900 sticky top-0 z-30">
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-700 text-white font-bold text-lg">D</span>
                <span class="font-bold tracking-wider text-base">DONS MEAT</span>
            </div>
            <button type="button" 
                    id="mobileMenuToggleBtn"
                    onclick="document.getElementById('mobileSidebar').classList.toggle('hidden')" 
                    class="p-2 rounded-lg bg-red-900/60 hover:bg-red-900 text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </header>

        <!-- Sidebar / Navigation -->
        <aside id="mobileSidebar" class="hidden lg:flex lg:flex-col lg:w-64 bg-red-950 text-slate-100 flex-shrink-0 min-h-screen border-r border-red-900/60 z-20">
            <!-- Brand -->
            <div class="h-16 flex items-center px-6 border-b border-red-900/40">
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br from-red-600 to-red-800 text-white font-bold text-xl shadow-inner">D</span>
                    <div>
                        <div class="font-bold tracking-wider text-base leading-tight">DONS MEAT</div>
                        <div class="text-[11px] text-red-300 font-medium tracking-wide">INTERNAL PANEL</div>
                    </div>
                </div>
            </div>

            <!-- User Info Badge -->
            <div class="px-6 py-4 border-b border-red-900/30 bg-red-900/20">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-full bg-red-800 flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'Pengguna' }}</p>
                        <div class="flex items-center mt-0.5">
                            @php
                                $roleBadgeClass = match(auth()->user()->role?->value) {
                                    'owner' => 'bg-amber-500/20 text-amber-300 border border-amber-500/30',
                                    'cashier' => 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30',
                                    default => 'bg-blue-500/20 text-blue-200 border border-blue-400/30',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $roleBadgeClass }}">
                                {{ auth()->user()->role?->label() ?? 'Admin' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-4 space-y-1.5 overflow-y-auto">
                @if(auth()->user()->isCashier())
                    <!-- Kasir Only Navigation -->
                    <div class="pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-emerald-300/80">Layanan Kasir</div>

                    <a href="{{ route('admin.direct-sales.create') }}" 
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.direct-sales.*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300 hover:bg-red-900/40 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Penjualan Langsung
                    </a>

                    <a href="{{ route('admin.orders.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.orders.*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300 hover:bg-red-900/40 hover:text-white' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            Pesanan Masuk
                        </div>
                        @if(isset($adminProcessingOrdersCount) && $adminProcessingOrdersCount > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-400 text-slate-900 animate-pulse">
                                {{ $adminProcessingOrdersCount }}
                            </span>
                        @endif
                    </a>

                @elseif(auth()->user()->isOwner())
                    <!-- Pemilik Only Navigation (Laporan Usaha & Acuan Restock) -->
                    <div class="pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-amber-300/80">Laporan Usaha Pemilik</div>

                    <a href="{{ route('admin.reports.sales') }}" 
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.reports.sales*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300 hover:bg-red-900/40 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Laporan Penjualan
                    </a>

                    <a href="{{ route('admin.reports.low-stock') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.reports.low-stock*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300 hover:bg-red-900/40 hover:text-white' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Stok Menipis (Acuan Restock)
                        </div>
                        @if(isset($adminLowStockCount) && $adminLowStockCount > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-500 text-white">
                                {{ $adminLowStockCount }}
                            </span>
                        @endif
                    </a>

                @else
                    <!-- Admin Full Operational & Management Navigation -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300 hover:bg-red-900/40 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>

                    <div class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-red-300/70">Operasional</div>

                    <a href="{{ route('admin.orders.index') }}" 
                       class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.orders.*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300/80 hover:bg-red-900/40 hover:text-white' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-red-400/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            Pesanan Masuk
                        </div>
                        @if(isset($adminProcessingOrdersCount) && $adminProcessingOrdersCount > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-400 text-slate-900 animate-pulse">
                                {{ $adminProcessingOrdersCount }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('admin.direct-sales.create') }}" 
                       class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.direct-sales.*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300/80 hover:bg-red-900/40 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 text-red-400/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Penjualan Langsung
                    </a>

                    <div class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-red-300/70">Katalog & Stok</div>

                    <a href="{{ route('admin.products.index') }}" 
                       class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.products.*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300/80 hover:bg-red-900/40 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 text-red-400/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Produk & Berat
                    </a>

                    <a href="{{ route('admin.categories.index') }}" 
                       class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.categories.*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300/80 hover:bg-red-900/40 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 text-red-400/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Kategori
                    </a>

                    <a href="{{ route('admin.stock.index') }}" 
                       class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.stock.*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300/80 hover:bg-red-900/40 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 text-red-400/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Persediaan Stok
                    </a>

                    <a href="{{ route('admin.delivery-zones.index') }}" 
                       class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.delivery-zones.*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300/80 hover:bg-red-900/40 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 text-red-400/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Zona & Ongkir
                    </a>

                    <div class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-red-300/70">Laporan & Audit</div>

                    <a href="{{ route('admin.reports.sales') }}" 
                       class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.reports.sales*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300/80 hover:bg-red-900/40 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 text-red-400/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Laporan Penjualan
                    </a>

                    <a href="{{ route('admin.reports.low-stock') }}" 
                       class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.reports.low-stock*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300/80 hover:bg-red-900/40 hover:text-white' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-red-400/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Stok Menipis
                        </div>
                        @if(isset($adminLowStockCount) && $adminLowStockCount > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-500 text-white">
                                {{ $adminLowStockCount }}
                            </span>
                        @endif
                    </a>

                    <div class="pt-3 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-blue-300/80">Pengaturan & Akun</div>

                    <a href="{{ route('admin.settings.edit') }}" 
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.settings.*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300 hover:bg-red-900/40 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Pengaturan Usaha
                    </a>

                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-red-800/80 text-white shadow-sm' : 'text-slate-300 hover:bg-red-900/40 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Manajemen User
                    </a>
                @endif
            </nav>

            <!-- Logout Button in Sidebar -->
            <div class="p-4 border-t border-red-900/40">
                <form method="POST" action="{{ route('admin.logout') }}" class="w-full">
                    @csrf
                    <button type="submit" 
                            id="sidebarLogoutBtn"
                            class="w-full flex items-center justify-center px-3 py-2 rounded-lg bg-red-900/60 hover:bg-red-900 text-slate-200 hover:text-white text-sm font-medium transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
            <!-- Topbar (Desktop) -->
            <header class="hidden lg:flex items-center justify-between h-16 px-8 bg-white border-b border-slate-200">
                <h1 class="text-xl font-bold text-slate-800">@yield('page-title', 'Dashboard')</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</span>
                    <div class="h-4 w-px bg-slate-200"></div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" 
                                id="topbarLogoutBtn"
                                class="text-sm font-medium text-red-600 hover:text-red-700 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <!-- Flash Messages -->
            <main class="flex-1 p-4 md:p-8 max-w-7xl w-full mx-auto">
                @if (session('status'))
                    <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-start">
                        <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 flex items-start">
                        <svg class="w-5 h-5 mr-3 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-semibold mb-1">Terjadi kesalahan:</p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

    </div>
</body>
</html>
