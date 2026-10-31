@extends('layouts.admin')

@section('title', 'Katalog Produk')
@section('page-title', 'Manajemen Produk Daging')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Daftar Produk Daging Sapi</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola produk, harga per kg, stok gram, batas minimum stok, dan pilihan berat.
            </p>
        </div>
        <a href="{{ route('admin.products.create') }}" 
           class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-red-700 hover:bg-red-800 text-white font-semibold text-sm shadow-sm transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Produk
        </a>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Cari nama atau SKU..." 
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-red-600">
            </div>

            <div>
                <select name="category_id" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-1 focus:ring-red-600">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center">
                <label class="inline-flex items-center text-xs font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} class="h-4 w-4 text-red-600 rounded border-slate-300">
                    <span class="ml-2">Hanya Stok Menipis</span>
                </label>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['search', 'category_id', 'low_stock']))
                    <a href="{{ route('admin.products.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Product Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5 text-left">Produk</th>
                        <th class="px-6 py-3.5 text-left">Kategori</th>
                        <th class="px-6 py-3.5 text-right">Harga / kg</th>
                        <th class="px-6 py-3.5 text-right">Stok Fisik / Tersedia</th>
                        <th class="px-6 py-3.5 text-center">Pilihan Berat</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Product Image & Name -->
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center text-slate-300">
                                        @if($product->image_path)
                                            <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span>🥩</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $product->name }}</div>
                                        <div class="text-[11px] font-mono text-slate-400">SKU: {{ $product->sku }}</div>
                                        @if($product->is_featured)
                                            <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Unggulan</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $product->category?->name ?? '-' }}
                                </span>
                            </td>

                            <!-- Price / kg -->
                            <td class="px-6 py-4 text-right font-extrabold text-red-800">
                                Rp {{ number_format($product->price_per_kg, 0, ',', '.') }}
                            </td>

                            <!-- Stock Gram -->
                            <td class="px-6 py-4 text-right">
                                <div class="font-bold {{ $product->isLowStock() ? 'text-amber-600' : 'text-slate-900' }}">
                                    {{ number_format($product->available_stock_gram / 1000, 1) }} kg
                                    <span class="text-xs font-normal text-slate-400">({{ number_format($product->available_stock_gram) }} g)</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    Fisik: {{ number_format($product->stock_gram) }} g | Cadangan: {{ number_format($product->reserved_stock_gram) }} g
                                </div>
                                @if($product->isLowStock())
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                        ⚠️ Stok Menipis
                                    </span>
                                @endif
                            </td>

                            <!-- Weight Options count -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-wrap justify-center gap-1">
                                    @forelse($product->weightOptions as $w)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $w->is_active ? 'bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-400 line-through' }}">
                                            {{ $w->label }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">Belum ada</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $product->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.products.edit', $product) }}" 
                                   class="text-xs font-semibold text-slate-700 hover:text-red-700 transition">
                                    Edit & Pilihan Berat
                                </a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                Tidak ada produk yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
