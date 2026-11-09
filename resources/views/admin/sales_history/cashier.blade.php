@extends('layouts.admin')

@section('title', 'Riwayat Penjualan Layanan Saya')
@section('page-title', 'Riwayat Penjualan Layanan Kasir')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 mb-1">
                Layanan Petugas: {{ auth()->user()->name }}
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Riwayat Penjualan Saya</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Rekapitulasi seluruh transaksi yang Anda layani, baik dari kasir toko langsung maupun penanganan pesanan masuk.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.my-sales-history.export', request()->all()) }}" 
               class="inline-flex items-center px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-bold transition shadow-xs gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Unduh Riwayat Saya (CSV)</span>
            </a>

            <a href="{{ route('admin.direct-sales.create') }}" 
               class="inline-flex items-center px-4 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white text-xs font-bold transition shadow-sm gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buka Kasir Transaksi Baru</span>
            </a>
        </div>
    </div>

    <!-- Personal Cashier Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Revenue by Cashier -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Omset Saya</span>
            <div class="text-2xl font-black text-slate-900 font-mono tracking-tight">
                Rp {{ number_format($myTotalRevenue, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-emerald-600 font-medium block">
                Khusus transaksi status Lunas
            </span>
        </div>

        <!-- Total Orders Handled -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Transaksi Dilayani</span>
            <div class="text-2xl font-black text-slate-900 font-mono tracking-tight">
                {{ number_format($myTotalOrders) }} <span class="text-sm font-normal text-slate-500">transaksi</span>
            </div>
            <span class="text-[11px] text-slate-500 block">
                Penjualan toko & pesanan masuk
            </span>
        </div>

        <!-- Total Meat Weight Handled -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Daging Disiapkan</span>
            <div class="text-2xl font-black text-red-700 font-mono tracking-tight">
                {{ number_format($myTotalWeightGram / 1000, 2, ',', '.') }} <span class="text-sm font-normal text-slate-500">kg</span>
            </div>
            <span class="text-[11px] text-slate-500 block">
                {{ number_format($myTotalWeightGram, 0, ',', '.') }} gram daging
            </span>
        </div>

        <!-- Today Sales by Cashier -->
        <div class="bg-gradient-to-br from-emerald-950 to-slate-900 p-5 rounded-2xl text-white shadow-md space-y-1 border border-emerald-900/60">
            <span class="text-xs font-semibold text-emerald-300 uppercase tracking-wider block">Penjualan Saya Hari Ini</span>
            <div class="text-2xl font-black text-amber-300 font-mono tracking-tight">
                Rp {{ number_format($myTodayRevenue, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-emerald-200 block">
                {{ number_format($myTodayOrders) }} transaksi hari ini
            </span>
        </div>

    </div>

    <!-- Filters & Search Form -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <form method="GET" action="{{ route('admin.my-sales-history.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            
            <!-- Start Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" 
                       name="start_date" 
                       value="{{ $startDate }}"
                       class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-red-600">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" 
                       name="end_date" 
                       value="{{ $endDate }}"
                       class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-red-600">
            </div>

            <!-- Channel Filter -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tipe Layanan</label>
                <select name="channel" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-red-600">
                    <option value="">Semua Layanan</option>
                    <option value="in_store" {{ request('channel') === 'in_store' ? 'selected' : '' }}>Penjualan Langsung (Toko)</option>
                    <option value="online" {{ request('channel') === 'online' ? 'selected' : '' }}>Pesanan Masuk</option>
                </select>
            </div>

            <!-- Search & Submit -->
            <div class="lg:col-span-2 flex items-end gap-1.5">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Nota / Pelanggan</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="No. nota / nama pelanggan..."
                           class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-red-600">
                </div>
                <button type="submit" 
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition">
                    Filter
                </button>
                @if(request()->anyFilled(['start_date', 'end_date', 'channel', 'search']))
                    <a href="{{ route('admin.my-sales-history.index') }}" 
                       class="p-2 text-xs text-slate-500 hover:text-red-700 border border-slate-200 rounded-xl hover:bg-slate-50 transition" 
                       title="Reset Filter">
                        ✕
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Personal Transactions Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs text-left">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-4 py-3">No. Nota / Waktu</th>
                        <th class="px-4 py-3">Tipe Layanan</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3 text-center">Item & Berat Daging</th>
                        <th class="px-4 py-3 text-right">Total Transaksi</th>
                        <th class="px-4 py-3 text-center">Status Pembayaran</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        @php
                            $totalWeightOrder = $order->items->sum('total_weight_gram');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            
                            <!-- No. Nota & Waktu -->
                            <td class="px-4 py-3.5">
                                <span class="font-mono font-bold text-slate-900 block text-xs">
                                    {{ $order->order_code }}
                                </span>
                                <span class="text-[11px] text-slate-500 block">
                                    {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                                </span>
                            </td>

                            <!-- Tipe Layanan -->
                            <td class="px-4 py-3.5">
                                @if($order->channel === \App\Enums\OrderChannel::InStore)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        🏪 Penjualan Langsung
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                        🌐 Pesanan Masuk ({{ $order->fulfillment_method?->label() }})
                                    </span>
                                @endif
                            </td>

                            <!-- Pelanggan -->
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-slate-900 block">{{ $order->customer_name }}</span>
                                <span class="text-[11px] text-slate-500 font-mono">{{ $order->customer_phone ?: '-' }}</span>
                            </td>

                            <!-- Item & Berat Daging -->
                            <td class="px-4 py-3.5 text-center">
                                <span class="font-bold text-slate-800 font-mono block">
                                    {{ number_format($totalWeightOrder) }} g
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    ({{ $order->items->count() }} paket &bull; {{ number_format($totalWeightOrder / 1000, 2) }} kg)
                                </span>
                            </td>

                            <!-- Total Transaksi -->
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900 text-sm">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                <span class="block text-[10px] font-normal text-slate-400 uppercase">
                                    {{ $order->payment?->payment_type ?? ($order->isCod() ? 'COD' : 'Lunas') }}
                                </span>
                            </td>

                            <!-- Status Pembayaran -->
                            <td class="px-4 py-3.5 text-center">
                                @if($order->isPaid())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        {{ $order->payment_status?->label() }}
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3.5 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($order->channel === \App\Enums\OrderChannel::InStore)
                                        <a href="{{ route('admin.direct-sales.show', $order) }}" 
                                           class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold transition"
                                           title="Lihat Nota Kasir">
                                            Nota
                                        </a>
                                    @else
                                        <a href="{{ route('admin.orders.show', $order) }}" 
                                           class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold transition"
                                           title="Lihat Detail Pesanan">
                                            Detail
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.orders.print', $order) }}" 
                                       target="_blank"
                                       class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold transition flex items-center gap-1"
                                       title="Cetak Struk / Label">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                        Struk
                                    </a>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                Belum ada transaksi penjualan yang Anda layani pada rentang tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

