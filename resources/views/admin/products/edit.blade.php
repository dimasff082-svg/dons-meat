@extends('layouts.admin')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk & Pilihan Berat')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900">Edit Produk: {{ $product->name }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">SKU: {{ $product->sku }} | Dibuat: {{ $product->created_at->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali ke Daftar
        </a>
    </div>

    <!-- Product Details Edit Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-sm font-semibold text-slate-700 mb-1">Kategori Produk</label>
                    <select name="category_id" id="category_id" required
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="sku" class="block text-sm font-semibold text-slate-700 mb-1">SKU</label>
                    <input type="text" name="sku" id="sku" required value="{{ old('sku', $product->sku) }}"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 font-mono">
                </div>
            </div>

            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Produk</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $product->name) }}"
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
            </div>

            <div>
                <label for="slug" class="block text-sm font-semibold text-slate-700 mb-1">Slug URL</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}"
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">{{ old('description', $product->description) }}</textarea>
            </div>

            <!-- Price and Threshold -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                <div>
                    <label for="price_per_kg" class="block text-sm font-semibold text-slate-700 mb-1">Harga per Kilogram (Rp)</label>
                    <input type="number" name="price_per_kg" id="price_per_kg" required min="0" step="any" value="{{ old('price_per_kg', $product->price_per_kg) }}"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm font-bold text-red-900 focus:ring-2 focus:ring-red-600">
                    <span class="text-[11px] text-slate-400 mt-1 block">Perubahan harga akan dicatat dalam histori</span>
                </div>

                <div>
                    <label for="low_stock_threshold_gram" class="block text-sm font-semibold text-slate-700 mb-1">Ambang Stok Menipis (Gram)</label>
                    <input type="number" name="low_stock_threshold_gram" id="low_stock_threshold_gram" required min="0" step="100" value="{{ old('low_stock_threshold_gram', $product->low_stock_threshold_gram) }}"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600">
                </div>

                <div class="sm:col-span-2">
                    <label for="price_change_reason" class="block text-xs font-semibold text-slate-600 mb-1">
                        Alasan Perubahan Harga (Bila mengubah harga)
                    </label>
                    <input type="text" name="price_change_reason" id="price_change_reason" placeholder="Contoh: Kenaikan harga pemasok daging karkas"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs">
                </div>
            </div>

            <!-- Image Upload & Preview -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Foto / Gambar Produk</label>
                <div class="flex items-center space-x-4">
                    <div class="w-20 h-20 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center flex-shrink-0">
                        @if($product->image_path)
                            <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-2xl text-slate-300">🥩</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <input type="file" name="image" id="image" accept="image/*"
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                        <span class="text-[11px] text-slate-400 mt-1 block">Biarkan kosong jika tidak ingin mengubah foto.</span>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="is_active" class="block text-sm font-semibold text-slate-700 mb-1">Status Penjualan</label>
                    <select name="is_active" id="is_active" required
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600">
                        <option value="1" {{ old('is_active', (string)(int)$product->is_active) === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', (string)(int)$product->is_active) === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div>
                    <label for="is_featured" class="block text-sm font-semibold text-slate-700 mb-1">Tampilkan di Beranda</label>
                    <select name="is_featured" id="is_featured" required
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600">
                        <option value="0" {{ old('is_featured', (string)(int)$product->is_featured) === '0' ? 'selected' : '' }}>Tidak</option>
                        <option value="1" {{ old('is_featured', (string)(int)$product->is_featured) === '1' ? 'selected' : '' }}>Ya (Unggulan)</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <button type="submit" 
                        class="px-6 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    Perbarui Data Produk
                </button>
            </div>
        </form>
    </div>

    <!-- Weight Options Management Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Pilihan Berat Produk</h3>
                <p class="text-xs text-slate-500">Kelola opsi berat yang dapat dipilih pelanggan untuk produk ini.</p>
            </div>
        </div>

        <!-- Existing Options Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Berat (Gram)</th>
                        <th class="px-4 py-3 text-left">Label Tampilan</th>
                        <th class="px-4 py-3 text-right">Harga Paket (Kalkulasi)</th>
                        <th class="px-4 py-3 text-center">Urutan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($product->weightOptions as $option)
                        <tr>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ number_format($option->weight_gram) }} g</td>
                            <td class="px-4 py-3 text-slate-700">{{ $option->label }}</td>
                            <td class="px-4 py-3 text-right font-extrabold text-red-800">
                                Rp {{ number_format($option->calculatePrice(), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $option->sort_order }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $option->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $option->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <form method="POST" action="{{ route('admin.products.weight-options.destroy', [$product, $option]) }}" class="inline" onsubmit="return confirm('Hapus opsi berat ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-400 text-xs">
                                Belum ada opsi berat untuk produk ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add New Weight Option Form -->
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Tambah Pilihan Berat Baru</h4>
            <form method="POST" action="{{ route('admin.products.weight-options.store', $product) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Berat (Gram)</label>
                    <input type="number" name="weight_gram" required min="50" step="50" placeholder="Contoh: 750"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Label (Opsional)</label>
                    <input type="text" name="label" placeholder="Contoh: 750 g"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                    <select name="is_active" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full py-2 px-3 bg-red-700 hover:bg-red-800 text-white rounded-lg text-xs font-bold transition">
                        + Tambah Opsi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Product Price Histories Log -->
    @if($product->priceHistories->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
            <h3 class="text-base font-bold text-slate-900">Histori Perubahan Harga Produk</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold">
                        <tr>
                            <th class="px-4 py-2.5 text-left">Waktu</th>
                            <th class="px-4 py-2.5 text-right">Harga Lama</th>
                            <th class="px-4 py-2.5 text-right">Harga Baru</th>
                            <th class="px-4 py-2.5 text-left">Diubah Oleh</th>
                            <th class="px-4 py-2.5 text-left">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($product->priceHistories as $history)
                            <tr>
                                <td class="px-4 py-2 text-slate-500">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2 text-right text-slate-600">Rp {{ number_format($history->old_price_per_kg, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right font-bold text-red-800">Rp {{ number_format($history->new_price_per_kg, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-slate-700">{{ $history->changedByUser?->name ?? 'Sistem' }}</td>
                                <td class="px-4 py-2 text-slate-600">{{ $history->reason ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
