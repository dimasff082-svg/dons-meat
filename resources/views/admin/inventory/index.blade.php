@extends('layouts.admin')

@section('title', 'Persediaan Stok')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Persediaan & Kontrol Stok</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau stok fisik daging, reservasi pesanan online, dan lakukan penyesuaian stok.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.stock.movements') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Riwayat Mutasi
            </a>
            <a href="{{ route('admin.stock.adjust.create') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-red-700 hover:bg-red-800 transition shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Penyesuaian Stok
            </a>
        </div>
    </div>

    <!-- Stock Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stok Fisik Total</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($totalPhysicalWeightGram / 1000, 2) }} <span class="text-xs font-normal text-slate-500">kg ({{ number_format($totalPhysicalWeightGram) }} g)</span></p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Dicadangkan (Online)</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($totalReservedWeightGram / 1000, 2) }} <span class="text-xs font-normal text-slate-500">kg ({{ number_format($totalReservedWeightGram) }} g)</span></p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stok Tersedia (Jual)</p>
                <p class="text-xl font-bold text-emerald-700 mt-0.5">{{ number_format($totalAvailableWeightGram / 1000, 2) }} <span class="text-xs font-normal text-slate-500">kg ({{ number_format($totalAvailableWeightGram) }} g)</span></p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $lowStockCount > 0 ? 'bg-red-50 text-red-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Produk Menipis</p>
                <p class="text-xl font-bold {{ $lowStockCount > 0 ? 'text-red-600' : 'text-slate-900' }} mt-0.5">{{ $lowStockCount }} <span class="text-xs font-normal text-slate-500">item</span></p>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.stock.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Cari Produk / SKU</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Nama produk atau SKU..."
                       class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Kategori</label>
                <select name="category_id" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Status Ketersediaan</label>
                <select name="filter" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">Semua Status</option>
                    <option value="low_stock" {{ request('filter') === 'low_stock' ? 'selected' : '' }}>Hanya Stok Menipis</option>
                    <option value="out_of_stock" {{ request('filter') === 'out_of_stock' ? 'selected' : '' }}>Hanya Stok Habis</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-slate-800 hover:bg-slate-900 rounded-lg transition shadow-sm">
                    Terapkan Filter
                </button>
                @if(request()->anyFilled(['search', 'category_id', 'filter']))
                    <a href="{{ route('admin.stock.index') }}" class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Stock Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Produk</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4 text-right">Stok Fisik</th>
                        <th class="py-3.5 px-4 text-right">Dicadangkan</th>
                        <th class="py-3.5 px-4 text-right">Stok Tersedia</th>
                        <th class="py-3.5 px-4 text-center">Batas Aman</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($products as $product)
                        @php
                            $available = $product->available_stock_gram;
                            $isOutOfStock = $available <= 0;
                            $isLow = $product->isLowStock();
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition {{ $isOutOfStock ? 'bg-red-50/30' : ($isLow ? 'bg-amber-50/20' : '') }}">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 shrink-0 flex items-center justify-center">
                                        @if($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-xs text-slate-400 font-semibold">{{ substr($product->name, 0, 2) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $product->name }}</div>
                                        <div class="text-xs text-slate-500 font-mono">{{ $product->sku }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $product->category->name ?? '-' }}
                            </td>

                            <td class="py-3.5 px-4 text-right font-medium text-slate-800">
                                {{ number_format($product->stock_gram) }} <span class="text-xs text-slate-400">g</span>
                                <div class="text-[11px] text-slate-500">({{ number_format($product->stock_gram / 1000, 2) }} kg)</div>
                            </td>

                            <td class="py-3.5 px-4 text-right text-slate-600">
                                @if($product->reserved_stock_gram > 0)
                                    <span class="font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        {{ number_format($product->reserved_stock_gram) }} g
                                    </span>
                                @else
                                    <span class="text-slate-400">0 g</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <span class="font-bold {{ $isOutOfStock ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-emerald-700') }}">
                                    {{ number_format($available) }} <span class="text-xs font-normal">g</span>
                                </span>
                                <div class="text-[11px] text-slate-500">({{ number_format($available / 1000, 2) }} kg)</div>
                            </td>

                            <td class="py-3.5 px-4 text-center text-xs text-slate-500 font-mono">
                                {{ number_format($product->low_stock_threshold_gram) }} g
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                @if($isOutOfStock)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                        Habis
                                    </span>
                                @elseif($isLow)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        Menipis
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Aman
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.stock.adjust.create', ['product_id' => $product->id]) }}" 
                                   class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-lg text-slate-700 bg-slate-100 hover:bg-red-50 hover:text-red-700 border border-slate-200 transition">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Sesuaikan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                Tidak ada data produk yang cocok dengan pencarian atau filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
