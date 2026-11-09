@extends('layouts.admin')

@section('title', 'Riwayat Seluruh Penjualan')
@section('page-title', 'Riwayat Seluruh Penjualan')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Riwayat Seluruh Penjualan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Data keseluruhan transaksi penjualan gerai langsung (POS) dan pesanan online DONS MEAT.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.sales-history.export', request()->all()) }}" 
               class="inline-flex items-center px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-bold transition shadow-xs gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Unduh Data (CSV)</span>
            </a>

            <a href="{{ route('admin.reports.sales') }}" 
               class="inline-flex items-center px-4 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white text-xs font-bold transition shadow-sm gap-1.5">
                <svg class="w-4 h-4 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Grafik & Analisis Penjualan</span>
            </a>
        </div>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Omset Penjualan</span>
            <div class="text-2xl font-black text-slate-900 font-mono tracking-tight">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-emerald-600 font-medium block">
                Khusus transaksi status Lunas
            </span>
        </div>

        <!-- Total Orders -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Transaksi</span>
            <div class="text-2xl font-black text-slate-900 font-mono tracking-tight">
                {{ number_format($totalOrdersCount) }} <span class="text-sm font-normal text-slate-500">transaksi</span>
            </div>
            <span class="text-[11px] text-slate-500 block">
                Periode filter terpilih
            </span>
        </div>

        <!-- Total Meat Weight -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Daging Terjual</span>
            <div class="text-2xl font-black text-red-700 font-mono tracking-tight">
                {{ number_format($totalWeightGram / 1000, 2, ',', '.') }} <span class="text-sm font-normal text-slate-500">kg</span>
            </div>
            <span class="text-[11px] text-slate-500 block">
                {{ number_format($totalWeightGram, 0, ',', '.') }} gram daging
            </span>
        </div>

        <!-- Today Sales -->
        <div class="bg-gradient-to-br from-red-950 to-slate-900 p-5 rounded-2xl text-white shadow-md space-y-1 border border-red-900/60">
            <span class="text-xs font-semibold text-red-300 uppercase tracking-wider block">Penjualan Hari Ini</span>
            <div class="text-2xl font-black text-amber-300 font-mono tracking-tight">
                Rp {{ number_format($todayRevenue, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-slate-300 block">
                {{ number_format($todayOrdersCount) }} transaksi hari ini
            </span>
        </div>

    </div>

    <!-- Filters & Search Form -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <form method="GET" action="{{ route('admin.sales-history.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            
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
                <label class="block text-xs font-semibold text-slate-600 mb-1">Kanal Penjualan</label>
                <select name="channel" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-red-600">
                    <option value="">Semua Kanal</option>
                    <option value="in_store" {{ request('channel') === 'in_store' ? 'selected' : '' }}>Penjualan Langsung (Toko)</option>
                    <option value="online" {{ request('channel') === 'online' ? 'selected' : '' }}>Pesanan Online</option>
                </select>
            </div>

            <!-- Staff / Cashier Filter -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Petugas / Kasir</label>
                <select name="cashier_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-red-600">
                    <option value="">Semua Petugas</option>
                    @foreach($staffMembers as $staff)
                        <option value="{{ $staff->id }}" {{ request('cashier_id') == $staff->id ? 'selected' : '' }}>
                            {{ $staff->name }} ({{ $staff->role?->label() }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Payment Status -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Bayar</label>
                <select name="payment_status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-red-600">
                    <option value="">Semua Status</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Lunas / COD</option>
                </select>
            </div>

            <!-- Search & Submit -->
            <div class="flex items-end gap-1.5">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Nota / Nama</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="No. nota / nama..."
                           class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-red-600">
                </div>
                <button type="submit" 
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition">
                    Filter
                </button>
                @if(request()->anyFilled(['start_date', 'end_date', 'channel', 'cashier_id', 'payment_status', 'search']))
                    <a href="{{ route('admin.sales-history.index') }}" 
                       class="p-2 text-xs text-slate-500 hover:text-red-700 border border-slate-200 rounded-xl hover:bg-slate-50 transition" 
                       title="Reset Filter">
                        ✕
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Transactions Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs text-left">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-4 py-3">No. Nota / Waktu</th>
                        <th class="px-4 py-3">Kanal & Kasir</th>
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

                            <!-- Kanal & Kasir -->
                            <td class="px-4 py-3.5">
                                @if($order->channel === \App\Enums\OrderChannel::InStore)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        🏪 Penjualan Langsung
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                        🌐 Pesanan Online
                                    </span>
                                @endif
                                <div class="text-[11px] text-slate-500 mt-1">
                                    Petugas: <strong class="text-slate-700">{{ $order->createdByUser->name ?? 'Online / Sistem' }}</strong>
                                </div>
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
                                    {{ $order->payment?->payment_type ?? ($order->isCod() ? 'COD' : 'Midtrans') }}
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
                                Tidak ada data penjualan yang cocok dengan filter tanggal atau kriteria pencarian.
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

