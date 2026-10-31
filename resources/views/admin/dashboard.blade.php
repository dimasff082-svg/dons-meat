@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Ringkasan Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Low Stock Alert Banner (If Any) -->
    @if($lowStockProducts > 0)
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-300 text-amber-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-xl bg-amber-100 text-amber-800 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-amber-800">Peringatan Persediaan</h4>
                    <p class="text-xs text-amber-900 mt-0.5">
                        Terdapat <strong>{{ $lowStockProducts }} variasi produk</strong> dengan stok tersedia di bawah batas minimum.
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.reports.low-stock') }}" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition shrink-0 text-center">
                Periksa Stok Menipis &rarr;
            </a>
        </div>
    @endif

    <!-- Store Status Banner & Standard Beef Price Banner -->
    <div class="bg-gradient-to-r from-red-900 via-red-950 to-slate-900 rounded-2xl p-6 text-white shadow-lg border border-red-800/40">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-700/60 text-red-200 border border-red-600/40 mb-2">
                    Operasional DONS MEAT
                </span>
                <h2 class="text-xl md:text-2xl font-extrabold tracking-tight">{{ $setting->business_name }}</h2>
                <p class="text-xs md:text-sm text-red-200/80 mt-1 max-w-xl">
                    {{ $setting->business_description ?? 'Sistem Informasi Penjualan Daging Sapi Segar & Berkualitas' }}
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 border border-white/10 text-right md:min-w-[220px]">
                <p class="text-xs uppercase tracking-wider text-red-200 font-semibold">Harga Standar Sapi/kg</p>
                <p class="text-2xl md:text-3xl font-extrabold text-amber-300 mt-1 font-mono">
                    Rp {{ number_format($setting->standard_beef_price_per_kg, 0, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-300 mt-0.5">Tampil di Beranda & Profil</p>
            </div>
        </div>
    </div>

    <!-- Stat Cards Grid (5 KPI Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Total Products -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Produk</span>
                <span class="p-2 rounded-xl bg-slate-100 text-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-bold text-slate-900 font-mono">{{ $totalProducts }}</div>
                <p class="text-[11px] text-slate-400 mt-1">Daging utuh & potongan</p>
            </div>
        </div>

        <!-- Orders Needing Attention (Processing) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm {{ $processingOrders > 0 ? 'ring-2 ring-amber-400 bg-amber-50/20' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-800">Perlu Disiapkan</span>
                <span class="p-2 rounded-xl bg-amber-100 text-amber-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black {{ $processingOrders > 0 ? 'text-amber-700 animate-pulse' : 'text-slate-900' }} font-mono">
                    {{ $processingOrders }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Sudah lunas, siap diproses</p>
            </div>
        </div>

        <!-- Low Stock Warning -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Stok Menipis</span>
                <span class="p-2 rounded-xl {{ $lowStockProducts > 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-bold {{ $lowStockProducts > 0 ? 'text-rose-600' : 'text-slate-900' }} font-mono">
                    {{ $lowStockProducts }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Di bawah ambang minimum</p>
            </div>
        </div>

        <!-- Today Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Omzet Hari Ini</span>
                <span class="p-2 rounded-xl bg-emerald-100 text-emerald-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-xl md:text-2xl font-bold text-emerald-700 font-mono">
                    Rp {{ number_format($todayRevenue, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">{{ $paidOrdersToday }} transaksi lunas hari ini</p>
            </div>
        </div>

        <!-- Month Revenue -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white p-5 rounded-2xl shadow-sm border border-slate-700/50">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-300">Omzet Bulan Ini</span>
                <span class="p-2 rounded-xl bg-white/10 text-emerald-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-xl md:text-2xl font-black text-amber-300 font-mono">
                    Rp {{ number_format($monthRevenue, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Periode {{ now()->translatedFormat('F Y') }}</p>
            </div>
        </div>

    </div>

    <!-- Main Operational Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Recent Orders Table Card (Span 2) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pesanan Masuk Terbaru</h3>
                    <p class="text-xs text-slate-500 mt-0.5">8 transaksi paling akhir dari seluruh kanal</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-red-700 hover:text-red-800 transition">
                    Lihat Semua Pesanan &rarr;
                </a>
            </div>

            @if($recentOrders->isEmpty())
                <div class="py-12 text-center text-slate-400">
                    <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm font-medium">Belum ada pesanan yang tercatat.</p>
                    <p class="text-xs mt-1 text-slate-400">Pesanan online dan penjualan langsung akan tampil di sini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-2.5 text-left">Kode & Waktu</th>
                                <th class="px-4 py-2.5 text-left">Pelanggan</th>
                                <th class="px-4 py-2.5 text-left">Kanal/Metode</th>
                                <th class="px-4 py-2.5 text-right">Total</th>
                                <th class="px-4 py-2.5 text-center">Status</th>
                                <th class="px-4 py-2.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-slate-900 hover:text-red-700">
                                            {{ $order->order_code }}
                                        </a>
                                        <span class="text-[10px] text-slate-400 block">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <strong class="text-slate-800 block">{{ $order->customer_name }}</strong>
                                        <span class="text-slate-500 text-[11px]">{{ $order->customer_phone }}</span>
                                    </td>
                                    <td class="px-4 py-3 space-y-0.5">
                                        <span class="inline-flex px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                            {{ $order->channel?->label() }}
                                        </span>
                                        <span class="text-[11px] text-slate-500 block">{{ $order->fulfillment_method?->label() }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold {{ $order->isPaid() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $order->order_status?->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-bold text-slate-700 hover:text-red-700">
                                            Detail &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Right Side: Low Stock Quick List & Operations (Span 1) -->
        <div class="space-y-6">

            <!-- Critical Low Stock Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Stok Paling Kritis</h3>
                    <a href="{{ route('admin.reports.low-stock') }}" class="text-xs font-semibold text-red-700 hover:text-red-800">
                        Lihat Semua
                    </a>
                </div>

                @if($lowStockList->isEmpty())
                    <p class="text-xs text-slate-400 py-4 text-center">Seluruh stok daging berada di atas batas aman.</p>
                @else
                    <ul class="divide-y divide-slate-100 text-xs">
                        @foreach($lowStockList as $lp)
                            @php
                                $avail = max(0, $lp->stock_gram - $lp->reserved_stock_gram);
                            @endphp
                            <li class="py-2.5 flex items-center justify-between">
                                <div>
                                    <strong class="text-slate-800 block truncate max-w-[150px]">{{ $lp->name }}</strong>
                                    <span class="text-slate-400 font-mono text-[10px]">{{ $lp->sku }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-mono font-bold {{ $avail <= 0 ? 'text-rose-600' : 'text-amber-700' }}">
                                        {{ number_format($avail / 1000, 2, ',', '.') }} kg
                                    </span>
                                    <span class="text-[10px] text-slate-400 block">Min: {{ number_format($lp->low_stock_threshold_gram / 1000, 2, ',', '.') }} kg</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Quick Info & Store Information -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-900 mb-3 border-b border-slate-100 pb-2">Informasi Operasional</h3>
                <dl class="space-y-2.5 text-xs">
                    <div>
                        <dt class="text-slate-500">Alamat Toko</dt>
                        <dd class="text-slate-800 mt-0.5 font-semibold">{{ $setting->address ?? 'Belum diatur' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Kontak WhatsApp</dt>
                        <dd class="text-slate-800 mt-0.5 font-semibold">{{ $setting->phone ?? 'Belum diatur' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Batas Waktu Pembayaran</dt>
                        <dd class="text-slate-800 mt-0.5 font-semibold">{{ $setting->payment_expiry_minutes }} menit</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Zona Waktu Sistem</dt>
                        <dd class="text-slate-800 mt-0.5 font-semibold">{{ $setting->timezone }}</dd>
                    </div>
                </dl>
            </div>

        </div>

    </div>

</div>
@endsection
