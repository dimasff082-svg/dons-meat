<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DONS MEAT — Daging Sapi Segar & Berkualitas</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans text-slate-800 antialiased">
    <!-- Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-red-700 to-red-900 text-white font-extrabold text-xl shadow-md">D</span>
                <div>
                    <span class="font-extrabold text-lg sm:text-xl tracking-wider text-slate-900">{{ $setting->business_name }}</span>
                    <span class="hidden sm:inline-block ml-2 text-xs text-red-700 font-semibold px-2 py-0.5 rounded-full bg-red-50 border border-red-200">Daging Sapi Pilihan</span>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.login') }}" 
                   class="inline-flex items-center px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">
                    <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Panel Internal
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1">
        <section class="relative bg-gradient-to-br from-red-950 via-red-900 to-slate-900 text-white py-16 sm:py-24 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center space-y-6">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-800/80 text-red-200 border border-red-700">
                    Sistem Informasi Penjualan Daging Sapi
                </span>
                
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                    Daging Sapi Segar & Berkualitas Langsung untuk Anda
                </h1>

                <p class="text-base sm:text-lg text-red-100/90 max-w-2xl mx-auto font-normal">
                    {{ $setting->business_description ?? 'Melayani pemesanan daging sapi utuh, potongan khusus, dan tulang dengan standar kualitas terbaik.' }}
                </p>

                <!-- Standard Beef Price Card -->
                <div class="pt-4 max-w-md mx-auto">
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20 shadow-xl">
                        <p class="text-xs font-semibold uppercase tracking-wider text-red-200">
                            Harga Standar Daging Sapi Saat Ini
                        </p>
                        <p class="text-3xl sm:text-4xl font-extrabold text-amber-300 mt-2">
                            Rp {{ number_format($setting->standard_beef_price_per_kg, 0, ',', '.') }} <span class="text-base sm:text-lg text-white font-medium">/ kg</span>
                        </p>
                        <p class="text-xs text-red-200/80 mt-1">
                            *Ditetapkan resmi oleh manajemen {{ $setting->business_name }}
                        </p>
                    </div>
                </div>

                <div class="pt-4 flex flex-wrap justify-center gap-4">
                    <a href="{{ route('admin.login') }}" 
                       class="px-6 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-sm sm:text-base shadow-lg transition">
                        Masuk Dashboard Internal
                    </a>
                </div>
            </div>
        </section>

        <!-- Quick Store Info Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center md:text-left">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-lg mb-3 mx-auto md:mx-0">
                        📍
                    </div>
                    <h3 class="font-bold text-slate-900 text-base">Alamat Usaha</h3>
                    <p class="text-sm text-slate-600 mt-1">{{ $setting->address ?? 'Makassar, Sulawesi Selatan' }}</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-lg mb-3 mx-auto md:mx-0">
                        ⏰
                    </div>
                    <h3 class="font-bold text-slate-900 text-base">Jam Operasional</h3>
                    <p class="text-sm text-slate-600 mt-1 whitespace-pre-line">{{ $setting->opening_hours ?? 'Senin - Sabtu: 08:00 - 18:00 WITA' }}</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-lg mb-3 mx-auto md:mx-0">
                        📞
                    </div>
                    <h3 class="font-bold text-slate-900 text-base">Kontak & WhatsApp</h3>
                    <p class="text-sm text-slate-600 mt-1">{{ $setting->phone ?? '08123456789' }}</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-8 border-t border-slate-800 text-center text-xs">
        <p>&copy; {{ date('Y') }} {{ $setting->business_name }}. Seluruh hak cipta dilindungi.</p>
    </footer>
</body>
</html>
