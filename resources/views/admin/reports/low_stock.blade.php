@extends('layouts.admin')

@section('title', 'Peringatan Stok Menipis')
@section('page-title', 'Peringatan Stok Menipis')

@section('content')
<div class="space-y-6">

    <!-- Header & Summary Banner -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Daftar Produk Stok Menipis</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Produk di bawah ini memiliki <strong>stok tersedia (stok fisik dikurangi stok cadangan)</strong> yang telah mencapai atau berada di bawah ambang batas minimum.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reports.low-stock.export') }}" 
                   id="downloadLowStockBtn"
                   class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh Acuan Restock (CSV)
                </a>

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.stock.adjust.create') }}" 
                       class="inline-flex items-center px-4 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white font-semibold text-xs transition shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        + Catat Penyesuaian Stok
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Alert List Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($products->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Semua Persediaan Aman</h3>
                <p class="text-xs text-slate-500 mt-1">Tidak ada produk daging sapi yang berada di bawah ambang batas minimum stok saat ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-5 py-3.5 text-left">Produk & SKU</th>
                            <th class="px-4 py-3.5 text-left">Kategori</th>
                            <th class="px-4 py-3.5 text-center">Stok Fisik</th>
                            <th class="px-4 py-3.5 text-center">Stok Cadangan</th>
                            <th class="px-4 py-3.5 text-center">Stok Tersedia</th>
                            <th class="px-4 py-3.5 text-center">Ambang Batas Minimum</th>
                            <th class="px-4 py-3.5 text-center">Kondisi</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($products as $product)
                            @php
                                $availGram = max(0, $product->stock_gram - $product->reserved_stock_gram);
                                $isOut = $availGram <= 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition {{ $isOut ? 'bg-rose-50/40' : '' }}">
                                <!-- Product & SKU -->
                                <td class="px-5 py-3.5">
                                    <strong class="text-slate-900 text-sm block">{{ $product->name }}</strong>
                                    <span class="text-slate-400 font-mono text-[11px]">{{ $product->sku }}</span>
                                </td>

                                <!-- Category -->
                                <td class="px-4 py-3.5 text-slate-700 font-medium">
                                    {{ $product->category ? $product->category->name : '-' }}
                                </td>

                                <!-- Physical Stock -->
                                <td class="px-4 py-3.5 text-center font-mono">
                                    <strong class="text-slate-800">{{ number_format($product->stock_gram / 1000, 2, ',', '.') }} kg</strong>
                                    <span class="text-[10px] text-slate-400 block">({{ number_format($product->stock_gram, 0, ',', '.') }} g)</span>
                                </td>

                                <!-- Reserved Stock -->
                                <td class="px-4 py-3.5 text-center font-mono text-amber-700">
                                    {{ number_format($product->reserved_stock_gram, 0, ',', '.') }} g
                                </td>

                                <!-- Available Stock -->
                                <td class="px-4 py-3.5 text-center font-mono">
                                    <strong class="{{ $isOut ? 'text-rose-700 font-black' : 'text-amber-800' }}">
                                        {{ number_format($availGram / 1000, 2, ',', '.') }} kg
                                    </strong>
                                    <span class="text-[10px] text-slate-400 block">({{ number_format($availGram, 0, ',', '.') }} g)</span>
                                </td>

                                <!-- Minimum Threshold -->
                                <td class="px-4 py-3.5 text-center font-mono text-slate-600">
                                    {{ number_format($product->low_stock_threshold_gram / 1000, 2, ',', '.') }} kg
                                    <span class="text-[10px] text-slate-400 block">({{ number_format($product->low_stock_threshold_gram, 0, ',', '.') }} g)</span>
                                </td>

                                <!-- Condition Badge -->
                                <td class="px-4 py-3.5 text-center">
                                    @if($isOut)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            Habis
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            Menipis
                                        </span>
                                    @endif
                                </td>

                                <!-- Action -->
                                <td class="px-5 py-3.5 text-right">
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('admin.stock.adjust.create') }}" 
                                           class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition">
                                            + Tambah Stok
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-slate-100 text-slate-600 text-[11px] font-medium">
                                            Perlu Restock
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

