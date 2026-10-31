@extends('layouts.admin')

@section('title', 'Riwayat Penjualan Langsung')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Penjualan Langsung Toko</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar transaksi offline/walk-in yang diselesaikan langsung oleh kasir/admin di toko fisik.</p>
        </div>
        <div>
            <a href="{{ route('admin.direct-sales.create') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-red-700 hover:bg-red-800 transition shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Buka Kasir Baru
            </a>
        </div>
    </div>

    <!-- Today Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Transaksi Langsung Hari Ini</p>
                <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ $todaySalesCount }} <span class="text-sm font-normal text-slate-500">struk</span></p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan Tunai/POS Hari Ini</p>
                <p class="text-2xl font-bold text-emerald-700 mt-0.5 font-mono">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.direct-sales.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Cari No. Nota / Pelanggan</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Contoh: DM-POS- / Budi..."
                       class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Transaksi</label>
                <input type="date" 
                       name="date" 
                       value="{{ request('date') }}" 
                       class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-slate-800 hover:bg-slate-900 rounded-lg transition shadow-sm">
                    Filter Riwayat
                </button>
                @if(request()->anyFilled(['search', 'date']))
                    <a href="{{ route('admin.direct-sales.index') }}" class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-4">No. Transaksi</th>
                        <th class="py-3.5 px-4">Waktu</th>
                        <th class="py-3.5 px-4">Pelanggan</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Item</th>
                        <th class="py-3.5 px-4 text-right">Total Transaksi</th>
                        <th class="py-3.5 px-4 text-center">Metode Bayar</th>
                        <th class="py-3.5 px-4">Kasir/Petugas</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($orders as $order)
                        @php
                            $payment = $order->payments->first();
                            $paymentType = match($payment->payment_type ?? 'cash') {
                                'cash' => 'Tunai',
                                'qris' => 'QRIS',
                                'manual_transfer' => 'Transfer',
                                default => strtoupper($payment->payment_type ?? 'Tunai'),
                            };
                            $totalWeight = $order->items->sum('total_weight_gram');
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ $order->order_code }}
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-500">
                                <div class="font-medium text-slate-800">{{ $order->created_at->translatedFormat('d M Y') }}</div>
                                <div class="text-[11px] font-mono">{{ $order->created_at->format('H:i') }} WITA</div>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-900">{{ $order->customer_name }}</div>
                                @if($order->customer_phone && $order->customer_phone !== '-')
                                    <div class="text-xs text-slate-500 font-mono">{{ $order->customer_phone }}</div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-center text-xs">
                                <span class="font-bold text-slate-800">{{ $order->items->count() }} item</span>
                                <div class="text-slate-500 font-mono">({{ number_format($totalWeight) }} g)</div>
                            </td>

                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-700">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $paymentType }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                {{ $order->createdByUser->name ?? 'Kasir' }}
                            </td>

                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.direct-sales.show', $order) }}" 
                                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Lihat Struk
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                Belum ada riwayat transaksi langsung di toko.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
