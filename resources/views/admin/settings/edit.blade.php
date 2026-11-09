@extends('layouts.admin')

@section('title', 'Pengaturan Usaha')
@section('page-title', 'Pengaturan Usaha & Harga Standar')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Pengaturan Usaha DONS MEAT</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Khusus Pemilik Usaha. Nilai harga standar akan ditampilkan di beranda dan halaman profil sebagai acuan resmi.
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Standard Beef Price Feature Card -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-red-950 to-slate-900 text-white space-y-3 border border-red-800/60 shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-red-300">
                        Acuan Harga Utama
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                        Khusus Pemilik
                    </span>
                </div>
                <label for="standard_beef_price_per_kg" class="block text-sm font-bold text-white">
                    Harga Acuan Standar Daging per Kilogram (Rp / kg)
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-3 text-red-300 font-bold text-sm">Rp</span>
                    <input type="number" 
                           name="standard_beef_price_per_kg" 
                           id="standard_beef_price_per_kg" 
                           required 
                           min="0" 
                           step="any"
                           value="{{ old('standard_beef_price_per_kg', $setting->standard_beef_price_per_kg) }}"
                           class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-amber-300 font-black text-xl sm:text-2xl focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <p class="text-xs text-red-200/80">
                    Nilai ini ditampilkan pada kartu hero beranda dan profil usaha. Tidak boleh diketik langsung pada template.
                </p>
            </div>

            <!-- Business Information -->
            <div class="space-y-4 pt-2">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Identitas Usaha</h3>

                <!-- Logo Toko -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-3">
                    <label class="block text-sm font-semibold text-slate-800">Logo Toko (Sistem)</label>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <!-- Current Logo Preview -->
                        <div class="w-20 h-20 rounded-2xl border-2 border-dashed border-slate-300 bg-white flex items-center justify-center overflow-hidden flex-shrink-0 shadow-xs relative">
                            @if($setting->logo_path)
                                <img id="logoPreviewImg" src="{{ asset('storage/' . $setting->logo_path) }}" alt="Logo Toko" class="w-full h-full object-contain p-1">
                            @else
                                <div id="logoDefaultPlaceholder" class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-700 to-red-900 text-white flex items-center justify-center font-extrabold text-2xl shadow-sm">
                                    D
                                </div>
                                <img id="logoPreviewImg" src="" alt="Logo Preview" class="w-full h-full object-contain p-1 hidden">
                            @endif
                        </div>

                        <!-- Upload Control -->
                        <div class="flex-1 space-y-2">
                            <input type="file" 
                                   name="logo" 
                                   id="logoInput" 
                                   accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                            <p class="text-[11px] text-slate-500">
                                Format: PNG, JPG, WEBP, atau SVG (Maks. 2MB). Ditampilkan di header, nota, dan faktur.
                            </p>
                            @if($setting->logo_path)
                                <label class="inline-flex items-center text-xs text-rose-600 hover:text-rose-700 cursor-pointer font-medium mt-1">
                                    <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 mr-1.5">
                                    Hapus logo custom & kembali ke logo default (D)
                                </label>
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <label for="business_name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Usaha</label>
                    <input type="text" name="business_name" id="business_name" required value="{{ old('business_name', $setting->business_name) }}"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600">
                </div>

                <div>
                    <label for="business_description" class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi Singkat Usaha</label>
                    <textarea name="business_description" id="business_description" rows="2"
                              class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600">{{ old('business_description', $setting->business_description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $setting->phone) }}"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email Toko</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $setting->email) }}"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600">
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-sm font-semibold text-slate-700 mb-1">Alamat Fisik Toko</label>
                    <textarea name="address" id="address" rows="2"
                              class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600">{{ old('address', $setting->address) }}</textarea>
                </div>

                <div>
                    <label for="opening_hours" class="block text-sm font-semibold text-slate-700 mb-1">Jam Operasional</label>
                    <textarea name="opening_hours" id="opening_hours" rows="2"
                              class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 font-mono text-xs">{{ old('opening_hours', $setting->opening_hours) }}</textarea>
                </div>
            </div>

            <!-- Order & System Config -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Ketentuan Pesanan & Waktu</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="payment_expiry_minutes" class="block text-sm font-semibold text-slate-700 mb-1">
                            Batas Waktu Pembayaran (Menit)
                        </label>
                        <input type="number" name="payment_expiry_minutes" id="payment_expiry_minutes" required min="5" max="1440" value="{{ old('payment_expiry_minutes', $setting->payment_expiry_minutes) }}"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600">
                        <span class="text-[11px] text-slate-400 mt-1 block">Pesanan belum dibayar akan otomatis kedaluwarsa setelah durasi ini</span>
                    </div>

                    <div>
                        <label for="timezone" class="block text-sm font-semibold text-slate-700 mb-1">Zona Waktu</label>
                        <input type="text" name="timezone" id="timezone" required value="{{ old('timezone', $setting->timezone) }}"
                               class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 font-mono">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" 
                        class="px-6 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('logoInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.getElementById('logoPreviewImg');
                const placeholder = document.getElementById('logoDefaultPlaceholder');
                if (img) {
                    img.src = event.target.result;
                    img.classList.remove('hidden');
                }
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
