@extends('layouts.admin')

@section('title', 'Riwayat Mutasi Stok')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.stock.index') }}" class="text-sm font-medium text-red-600 hover:text-red-700 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Stok
                </a>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Buku Besar Mutasi Stok</h1>
            <p class="text-sm text-slate-500 mt-1">Audit log lengkap seluruh pergerakan persediaan fisik dan reservasi pesanan.</p>
        </div>
        <div>
            <a href="{{ route('admin.stock.adjust.create') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-red-700 hover:bg-red-800 transition shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Catat Penyesuaian
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.stock.movements') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Filter Produk</label>
                <select name="product_id" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">Semua Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->sku }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Jenis Mutasi</label>
                <select name="movement_type" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">Semua Jenis Mutasi</option>
                    @foreach(\App\Enums\StockMovementType::cases() as $type)
                        <option value="{{ $type->value }}" {{ request('movement_type') === $type->value ? 'selected' : '' }}>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-slate-800 hover:bg-slate-900 rounded-lg transition shadow-sm">
                    Filter Mutasi
                </button>
                @if(request()->anyFilled(['product_id', 'movement_type']))
                    <a href="{{ route('admin.stock.movements') }}" class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Waktu</th>
                        <th class="py-3.5 px-4">Produk</th>
                        <th class="py-3.5 px-4">Jenis Mutasi</th>
                        <th class="py-3.5 px-4 text-right">Perubahan Stok Fisik</th>
                        <th class="py-3.5 px-4 text-right">Perubahan Cadangan</th>
                        <th class="py-3.5 px-4 text-right">Saldo Akhir (Fisik)</th>
                        <th class="py-3.5 px-4">Alasan / Keterangan</th>
                        <th class="py-3.5 px-4">Pencatat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($movements as $m)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-500">
                                <div class="font-medium text-slate-800">{{ $m->created_at->translatedFormat('d M Y') }}</div>
                                <div class="text-[11px] font-mono">{{ $m->created_at->format('H:i:s') }}</div>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-900">{{ $m->product->name ?? 'Produk Dihapus' }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $m->product->sku ?? '-' }}</div>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @php
                                    $typeVal = $m->movement_type->value ?? $m->movement_type;
                                    $badgeColor = match($typeVal) {
                                        'inbound', 'adjustment_in' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        'sale' => 'bg-blue-50 text-blue-800 border-blue-200',
                                        'adjustment_out', 'damaged' => 'bg-red-50 text-red-800 border-red-200',
                                        'reserve' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'release_reservation' => 'bg-purple-50 text-purple-800 border-purple-200',
                                        default => 'bg-slate-50 text-slate-800 border-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $badgeColor }}">
                                    {{ $m->movement_type->label() }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-right whitespace-nowrap font-mono font-medium">
                                @if($m->stock_delta_gram > 0)
                                    <span class="text-emerald-600 font-semibold">+{{ number_format($m->stock_delta_gram) }} g</span>
                                @elseif($m->stock_delta_gram < 0)
                                    <span class="text-red-600 font-semibold">{{ number_format($m->stock_delta_gram) }} g</span>
                                @else
                                    <span class="text-slate-400">0 g</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-right whitespace-nowrap font-mono text-xs">
                                @if($m->reserved_delta_gram > 0)
                                    <span class="text-amber-600 font-semibold">+{{ number_format($m->reserved_delta_gram) }} g</span>
                                @elseif($m->reserved_delta_gram < 0)
                                    <span class="text-purple-600 font-semibold">{{ number_format($m->reserved_delta_gram) }} g</span>
                                @else
                                    <span class="text-slate-400">0 g</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-right whitespace-nowrap font-medium text-slate-800">
                                {{ number_format($m->stock_balance_after_gram) }} <span class="text-xs text-slate-400">g</span>
                                <div class="text-[11px] text-slate-500">({{ number_format($m->stock_balance_after_gram / 1000, 2) }} kg)</div>
                            </td>

                            <td class="py-3.5 px-4 text-slate-700 max-w-xs break-words text-xs">
                                <div>{{ $m->reason }}</div>
                                @if($m->order)
                                    <span class="inline-block mt-1 text-[11px] font-mono text-blue-600 hover:underline">
                                        #{{ $m->order->order_code }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-600">
                                {{ $m->createdByUser->name ?? 'Sistem / Webhook' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                Belum ada riwayat mutasi stok yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
