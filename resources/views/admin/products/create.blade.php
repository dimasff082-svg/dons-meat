@extends('layouts.admin')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk Daging')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-extrabold text-slate-900">Tambah Produk Daging Baru</h2>
        <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Category & SKU -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-sm font-semibold text-slate-700 mb-1">Kategori Produk</label>
                    <select name="category_id" id="category_id" required
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="sku" class="block text-sm font-semibold text-slate-700 mb-1">
                        SKU (Opsional, otomatis bila kosong)
                    </label>
                    <input type="text" name="sku" id="sku" value="{{ old('sku') }}" placeholder="DM-XXXXXX"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 font-mono">
                </div>
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Produk Daging</label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                       placeholder="Contoh: Daging Sapi Tenderloin / Has Dalam"
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi Produk</label>
                <textarea name="description" id="description" rows="3"
                          placeholder="Penjelasan kualitas daging, tekstur, atau rekomendasi masakan..."
                          class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">{{ old('description') }}</textarea>
            </div>

            <!-- Price and Stock Gram -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                <div>
                    <label for="price_per_kg" class="block text-sm font-semibold text-slate-700 mb-1">Harga per Kilogram (Rp)</label>
                    <input type="number" name="price_per_kg" id="price_per_kg" required min="0" step="500" value="{{ old('price_per_kg') }}"
                           placeholder="150000"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 font-bold">
                    <span class="text-[11px] text-slate-400 mt-1 block">Harga dasar untuk 1.000 g</span>
                </div>

                <div>
                    <label for="stock_gram" class="block text-sm font-semibold text-slate-700 mb-1">Stok Awal Fisik (Gram)</label>
                    <input type="number" name="stock_gram" id="stock_gram" required min="0" step="100" value="{{ old('stock_gram', 10000) }}"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600 font-bold">
                    <span class="text-[11px] text-slate-400 mt-1 block">10.000 g = 10 kg</span>
                </div>

                <div>
                    <label for="low_stock_threshold_gram" class="block text-sm font-semibold text-slate-700 mb-1">Ambang Stok Menipis (Gram)</label>
                    <input type="number" name="low_stock_threshold_gram" id="low_stock_threshold_gram" required min="0" step="100" value="{{ old('low_stock_threshold_gram', 2000) }}"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
                    <span class="text-[11px] text-slate-400 mt-1 block">Peringatan saat stok &le; nilai ini</span>
                </div>
            </div>

            <!-- Default Weight Options Checkboxes -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                <span class="block text-sm font-semibold text-slate-700">Pilihan Berat yang Diaktifkan Awal</span>
                <p class="text-xs text-slate-500">Pilih berat paket yang langsung disediakan untuk pelanggan:</p>
                <div class="flex flex-wrap gap-4 pt-1">
                    <label class="inline-flex items-center text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="default_weights[]" value="250" checked class="h-4 w-4 text-red-600 rounded border-slate-300">
                        <span class="ml-2">250 gram</span>
                    </label>
                    <label class="inline-flex items-center text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="default_weights[]" value="500" checked class="h-4 w-4 text-red-600 rounded border-slate-300">
                        <span class="ml-2">500 gram (0,5 kg)</span>
                    </label>
                    <label class="inline-flex items-center text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="default_weights[]" value="1000" checked class="h-4 w-4 text-red-600 rounded border-slate-300">
                        <span class="ml-2">1.000 gram (1 kg)</span>
                    </label>
                    <label class="inline-flex items-center text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="default_weights[]" value="2000" class="h-4 w-4 text-red-600 rounded border-slate-300">
                        <span class="ml-2">2.000 gram (2 kg)</span>
                    </label>
                </div>
            </div>

            <!-- Image Upload -->
            <div>
                <label for="image" class="block text-sm font-semibold text-slate-700 mb-1">Foto / Gambar Produk</label>
                <input type="file" name="image" id="image" accept="image/*"
                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                <span class="text-[11px] text-slate-400 mt-1 block">Format: JPG, PNG, WEBP. Maksimal 2MB.</span>
            </div>

            <!-- Status Checkboxes -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="is_active" class="block text-sm font-semibold text-slate-700 mb-1">Status Penjualan</label>
                    <select name="is_active" id="is_active" required
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
                        <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Aktif (Dapat Dipesan)</option>
                        <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Nonaktif (Disembunyikan)</option>
                    </select>
                </div>

                <div>
                    <label for="is_featured" class="block text-sm font-semibold text-slate-700 mb-1">Tampilkan di Beranda?</label>
                    <select name="is_featured" id="is_featured" required
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
                        <option value="0" {{ old('is_featured', '0') === '0' ? 'selected' : '' }}>Tidak</option>
                        <option value="1" {{ old('is_featured') === '1' ? 'selected' : '' }}>Ya (Produk Unggulan)</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <a href="{{ route('admin.products.index') }}" 
                   class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
