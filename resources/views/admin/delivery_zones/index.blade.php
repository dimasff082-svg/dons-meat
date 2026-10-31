@extends('layouts.admin')

@section('title', 'Zona Pengiriman & Ongkir')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Zona Pengiriman & Ongkos Kirim</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola zona wilayah antar daging segar dan tarif ongkos kirim flat per zona.</p>
        </div>
        <div>
            <a href="{{ route('admin.delivery-zones.create') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-red-700 hover:bg-red-800 transition shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah Zona Baru
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Zones Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Nama Zona</th>
                        <th class="py-3.5 px-4">Cakupan Wilayah / Deskripsi</th>
                        <th class="py-3.5 px-4 text-right">Tarif Ongkir</th>
                        <th class="py-3.5 px-4 text-center">Pesanan</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($zones as $zone)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $zone->name }}
                            </td>

                            <td class="py-3.5 px-4 text-slate-600 text-xs max-w-sm">
                                {{ $zone->description ?? 'Semua area yang terjangkau kurir toko' }}
                            </td>

                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-800">
                                Rp {{ number_format($zone->fee, 0, ',', '.') }}
                            </td>

                            <td class="py-3.5 px-4 text-center text-xs text-slate-500 font-mono">
                                {{ $zone->orders_count }} pesanan
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <form method="POST" action="{{ route('admin.delivery-zones.toggle-active', $zone) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border transition {{ $zone->is_active ? 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200' }}"
                                            title="Klik untuk ubah status">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $zone->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $zone->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>

                            <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('admin.delivery-zones.edit', $zone) }}" 
                                   class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-lg text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                                    Edit
                                </a>

                                <form method="POST" action="{{ route('admin.delivery-zones.destroy', $zone) }}" class="inline" onsubmit="return confirm('Hapus zona pengiriman ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Belum ada zona pengiriman. Silakan tambahkan zona untuk mengaktifkan opsi pengiriman ke alamat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($zones->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $zones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
