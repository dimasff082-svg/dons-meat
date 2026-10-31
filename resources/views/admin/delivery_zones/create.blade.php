@extends('layouts.admin')

@section('title', 'Tambah Zona Pengiriman')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.delivery-zones.index') }}" class="text-sm font-medium text-red-600 hover:text-red-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Zona
        </a>
    </div>

    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Zona Pengiriman</h1>
        <p class="text-sm text-slate-500 mt-1">Tentukan nama wilayah jangkauan dan tarif flat ongkos kirim.</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.delivery-zones.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold text-slate-800 mb-1">
                    Nama Zona <span class="text-red-600">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}" 
                       placeholder="Contoh: Makassar Kota - Zona 1 (Panakkukang, Rappocini)"
                       required 
                       class="w-full px-3.5 py-2.5 text-sm rounded-lg border @error('name') border-red-500 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-red-500">
                @error('name')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fee" class="block text-sm font-semibold text-slate-800 mb-1">
                    Tarif Ongkos Kirim (Rp) <span class="text-red-600">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm font-mono">
                        Rp
                    </div>
                    <input type="number" 
                           name="fee" 
                           id="fee" 
                           value="{{ old('fee', 15000) }}" 
                           min="0" 
                           step="1000"
                           required 
                           class="w-full pl-10 pr-3.5 py-2.5 text-sm font-mono rounded-lg border @error('fee') border-red-500 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                @error('fee')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-slate-800 mb-1">
                    Cakupan Area / Keterangan (Opsional)
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="3" 
                          placeholder="Contoh: Meliputi seluruh kelurahan di Kec. Panakkukang dan Rappocini. Pengiriman menggunakan kurir instan toko."
                          class="w-full px-3.5 py-2 text-sm rounded-lg border @error('description') border-red-500 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                    <span class="text-sm font-medium text-slate-700">Aktifkan zona ini (dapat dipilih pelanggan saat checkout pengiriman)</span>
                </label>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.delivery-zones.index') }}" class="px-4 py-2 text-sm font-medium rounded-lg text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 text-sm font-medium rounded-lg text-white bg-red-700 hover:bg-red-800 transition shadow-sm">
                    Simpan Zona
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
