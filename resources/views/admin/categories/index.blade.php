@extends('layouts.admin')

@section('title', 'Kategori Produk')
@section('page-title', 'Kategori Produk')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Kategori Produk Daging</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola kategori daging sapi (Daging Utuh, Potongan Khusus, Tulang).
            </p>
        </div>
        <a href="{{ route('admin.categories.create') }}" 
           class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-red-700 hover:bg-red-800 text-white font-semibold text-sm shadow-sm transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kategori
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold">
                    <tr>
                        <th class="px-6 py-3.5 text-left">Nama Kategori</th>
                        <th class="px-6 py-3.5 text-left">Slug</th>
                        <th class="px-6 py-3.5 text-center">Jumlah Produk</th>
                        <th class="px-6 py-3.5 text-center">Urutan</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $category->name }}</td>
                            <td class="px-6 py-4 text-slate-500 font-mono text-xs">{{ $category->slug }}</td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-700">
                                <span class="px-2.5 py-1 rounded-full text-xs bg-slate-100 border border-slate-200">
                                    {{ $category->products_count }} produk
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-slate-600">{{ $category->sort_order }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $category->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" 
                                   class="text-xs font-semibold text-slate-700 hover:text-red-700 transition">
                                    Edit
                                </a>
                                @if($category->products_count === 0)
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                Belum ada kategori produk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
