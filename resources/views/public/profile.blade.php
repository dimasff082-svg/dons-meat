@extends('layouts.public')

@section('title', 'Tentang Kami & Informasi Usaha')
@section('meta_description', 'Profil dan informasi operasional DONS MEAT, penyedia daging sapi segar, alamat toko, jam buka, dan harga acuan per kg.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-12">
    
    <!-- Header -->
    <div class="text-center space-y-3">
        <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-red-100 text-red-800">
            Profil & Informasi Usaha
        </span>
        <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight">
            {{ $setting->business_name }}
        </h1>
        <p class="text-slate-600 max-w-xl mx-auto text-sm sm:text-base">
            {{ $setting->business_description ?? 'Penyedia daging sapi berkualitas, higienis, dan terpercaya untuk kebutuhan rumah tangga maupun usaha kuliner.' }}
        </p>
    </div>

    <!-- Official Standard Beef Price Feature -->
    <div class="bg-gradient-to-br from-red-900 to-slate-900 rounded-3xl p-8 text-white shadow-xl text-center space-y-3">
        <span class="text-xs font-semibold uppercase tracking-widest text-red-300">
            Harga Acuan Resmi
        </span>
        <h2 class="text-xl sm:text-2xl font-bold">Harga Standar Daging Sapi / kg</h2>
        <div class="text-4xl sm:text-5xl font-black text-amber-300">
            Rp {{ number_format($setting->standard_beef_price_per_kg, 0, ',', '.') }}
        </div>
        <p class="text-xs text-red-200/80 max-w-md mx-auto">
            Harga standar yang berlaku hari ini. Nilai ini merupakan harga acuan resmi dan dapat diperbarui oleh manajemen DONS MEAT.
        </p>
    </div>

    <!-- Information Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-lg">
                📍
            </div>
            <h3 class="font-bold text-base text-slate-900">Alamat Usaha</h3>
            <p class="text-sm text-slate-600 leading-relaxed">
                {{ $setting->address ?? 'Jl. Pengayoman, Makassar, Sulawesi Selatan' }}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-lg">
                ⏰
            </div>
            <h3 class="font-bold text-base text-slate-900">Jam Operasional</h3>
            <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                {{ $setting->opening_hours ?? "Senin - Sabtu: 08:00 - 18:00 WITA\nMinggu: 08:00 - 15:00 WITA" }}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-lg">
                📞
            </div>
            <h3 class="font-bold text-base text-slate-900">Kontak Resmi</h3>
            <p class="text-sm text-slate-600 leading-relaxed">
                WhatsApp / Telepon: <span class="font-bold text-slate-800">{{ $setting->phone ?? '08123456789' }}</span><br>
                @if($setting->email)
                    Email: <span class="font-bold text-slate-800">{{ $setting->email }}</span>
                @endif
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-lg">
                🚚
            </div>
            <h3 class="font-bold text-base text-slate-900">Metode Pemenuhan</h3>
            <p class="text-sm text-slate-600 leading-relaxed">
                • <strong>Ambil di Toko:</strong> Gratis, disiapkan sesuai jadwal pesanan.<br>
                • <strong>Pengiriman Kurir:</strong> Tersedia untuk area pengiriman yang ditentukan.
            </p>
        </div>

    </div>

    <!-- CTA -->
    <div class="text-center pt-6">
        <a href="{{ route('catalog.index') }}" 
           class="inline-flex items-center px-8 py-3.5 rounded-xl bg-red-700 hover:bg-red-800 text-white font-bold text-sm shadow-md transition">
            Lihat Katalog & Mulai Pesan
        </a>
    </div>

</div>
@endsection
