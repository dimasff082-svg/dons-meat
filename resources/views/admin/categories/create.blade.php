@extends('layouts.admin')

@section('title', 'Tambah Kategori')
@section('page-title', 'Tambah Kategori Produk')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-extrabold text-slate-900">Tambah Kategori Baru</h2>
        <a href="{{ route('admin.categories.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Kategori</label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                       placeholder="Contoh: Potongan Khusus"
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
            </div>

            <div>
                <label for="slug" class="block text-sm font-semibold text-slate-700 mb-1">
                    Slug URL (Opsional, otomatis dari nama)
                </label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                       placeholder="contoh: potongan-khusus"
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi</label>
                <textarea name="description" id="description" rows="3"
                          placeholder="Penjelasan singkat mengenai kategori ini..."
                          class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="sort_order" class="block text-sm font-semibold text-slate-700 mb-1">Urutan Tampilan</label>
                    <input type="number" name="sort_order" id="sort_order" min="0" value="{{ old('sort_order', 0) }}"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
                </div>

                <div>
                    <label for="is_active" class="block text-sm font-semibold text-slate-700 mb-1">Status Kategori</label>
                    <select name="is_active" id="is_active" required
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
                        <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Aktif (Tampil di Katalog)</option>
                        <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <a href="{{ route('admin.categories.index') }}" 
                   class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
