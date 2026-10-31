@extends('layouts.admin')

@section('title', 'Laporan Penjualan & Omzet')
@section('page-title', 'Laporan Penjualan')

@section('content')
<div class="space-y-6">

    <!-- Header & Date Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Laporan Penjualan & Pendapatan Omzet</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Omzet dihitung secara akurat hanya dari pesanan yang berstatus <strong>Lunas (Paid)</strong> dalam periode tanggal yang dipilih.
                </p>
            </div>

            <!-- Export CSV Action Button -->
            <div>
                <a href="{{ route('admin.reports.sales.export', request()->all()) }}" 
                   class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs transition shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Ekspor CSV Laporan
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.reports.sales') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-1">
            <!-- Start Date -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Channel -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Kanal Penjualan</label>
                <select name="channel" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Semua Kanal (Gabungan)</option>
                    <option value="online" {{ $selectedChannel === 'online' ? 'selected' : '' }}>Online Website</option>
                    <option value="in_store" {{ $selectedChannel === 'in_store' ? 'selected' : '' }}>Penjualan Langsung Toko</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="sm:col-span-2 flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition">
                    Terapkan Filter
                </button>
                <a href="{{ route('admin.reports.sales') }}" class="py-2 px-4 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition">
                    Bulan Berjalan
                </a>
            </div>
        </form>
    </div>

    <!-- 5 KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Total Omzet -->
        <div class="bg-gradient-to-br from-emerald-800 to-teal-950 p-5 rounded-2xl text-white shadow-sm border border-emerald-700/40">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-200">Total Omzet Lunas</span>
            <div class="text-xl md:text-2xl font-black mt-2 font-mono text-emerald-100">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-emerald-200/80 mt-1">Gabungan seluruh kanal</p>
        </div>

        <!-- Omzet Online -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Omzet Online Website</span>
            <div class="text-xl md:text-2xl font-bold mt-2 font-mono text-sky-700">
                Rp {{ number_format($onlineRevenue, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">{{ $onlinePaidOrders }} transaksi lunas</p>
        </div>

        <!-- Omzet In-Store -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Omzet Penjualan Langsung</span>
            <div class="text-xl md:text-2xl font-bold mt-2 font-mono text-purple-700">
                Rp {{ number_format($inStoreRevenue, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">{{ $inStorePaidOrders }} transaksi toko</p>
        </div>

        <!-- Total Paid Orders -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Jumlah Transaksi Lunas</span>
            <div class="text-xl md:text-2xl font-bold mt-2 font-mono text-slate-900">
                {{ number_format($totalPaidOrders, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Pesanan terselesaikan</p>
        </div>

        <!-- Average Order Value (AOV) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Rata-rata Transaksi (AOV)</span>
            <div class="text-xl md:text-2xl font-bold mt-2 font-mono text-amber-700">
                Rp {{ number_format($averageOrderValue, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Per transaksi lunas</p>
        </div>

    </div>

    <!-- Daily Breakdown Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Rekapitulasi Omzet Harian</h3>
            <span class="text-xs text-slate-500">Periode: {{ Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
        </div>

        @if($dailyBreakdown->isEmpty())
            <p class="text-xs text-slate-400 py-4 text-center">Belum ada data transaksi lunas pada rentang tanggal ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-right">Omzet Online (Rp)</th>
                            <th class="px-4 py-3 text-right">Omzet Toko (Rp)</th>
                            <th class="px-4 py-3 text-right">Total Omzet (Rp)</th>
                            <th class="px-4 py-3 text-center">Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($dailyBreakdown as $row)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-2.5 font-medium text-slate-900">
                                    {{ Carbon\Carbon::parse($row->report_date)->translatedFormat('l, d F Y') }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono text-slate-700">
                                    {{ number_format($row->online_total, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono text-slate-700">
                                    {{ number_format($row->instore_total, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono font-bold text-emerald-700">
                                    Rp {{ number_format($row->daily_total, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2.5 text-center font-semibold text-slate-800">
                                    {{ $row->total_count }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Transaction List Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Daftar Transaksi Periode Terpilih</h3>
            <span class="text-xs text-slate-500">Semua pesanan yang tercatat</span>
        </div>

        @if($transactions->isEmpty())
            <p class="text-xs text-slate-400 py-6 text-center">Tidak ada transaksi yang tercatat pada periode ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Kode Order</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Pelanggan</th>
                            <th class="px-4 py-3 text-left">Kanal</th>
                            <th class="px-4 py-3 text-left">Pemenuhan</th>
                            <th class="px-4 py-3 text-center">Status Bayar</th>
                            <th class="px-4 py-3 text-center">Status Pesanan</th>
                            <th class="px-4 py-3 text-right">Total Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($transactions as $t)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-2.5 font-mono font-bold">
                                    <a href="{{ route('admin.orders.show', $t) }}" class="text-slate-900 hover:text-red-700">
                                        {{ $t->order_code }}
                                    </a>
                                </td>
                                <td class="px-4 py-2.5 text-slate-600">
                                    {{ $t->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-2.5 font-semibold text-slate-800">
                                    {{ $t->customer_name }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $t->channel === \App\Enums\OrderChannel::Online ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $t->channel?->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-600">
                                    {{ $t->fulfillment_method?->label() }}
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold {{ $t->isPaid() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $t->payment_status?->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        {{ $t->order_status?->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($t->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="pt-3 border-t border-slate-100">
                    {{ $transactions->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection

