@extends('layouts.public')

@section('title', 'Tentang Kami & Informasi Usaha')
@section('meta_description', 'Profil lengkap DONS MEAT — PT Urwah Barokah Sukses, penyedia aneka daging berkualitas tinggi, bersertifikat Halal Indonesia & NKV, dengan kemasan praktis higienis.')

@section('content')
<div class="space-y-16 sm:space-y-24 py-12 sm:py-16">

    <!-- 1. Header & Hero Profile -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
        <div class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-red-100 text-red-800 border border-red-200">
            Profil Resmi & Komitmen Kualitas
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-tight">
            Mengenal Lebih Dekat <span class="text-red-700">{{ $setting->business_name }}</span>
        </h1>

        <p class="text-base sm:text-lg text-slate-600 max-w-3xl mx-auto leading-relaxed">
            Di bawah naungan <strong>PT Urwah Barokah Sukses</strong>, DONS MEAT hadir sebagai pusat penyedia dan distributor daging segar serta olahan berkualitas tinggi. Kami berkomitmen menyajikan daging yang aman, sehat, utuh, dan halal (ASUH) untuk keluarga dan mitra usaha kuliner.
        </p>
    </section>

    <!-- 2. Facility & Cold Chain Logistics -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative bg-slate-900 rounded-3xl overflow-hidden border border-slate-800 shadow-2xl">
            <div class="grid grid-cols-1 lg:grid-cols-12 items-center">
                
                <!-- Facility Image -->
                <div class="lg:col-span-7 h-72 sm:h-96 lg:h-full min-h-[340px] relative overflow-hidden">
                    <img src="{{ asset('images/hero-meat.jpg') }}" 
                         alt="Gedung Pusat DONS MEAT - PT Urwah Barokah Sukses" 
                         class="w-full h-full object-cover object-center filter brightness-95 contrast-105 hover:scale-105 transition duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent lg:hidden"></div>
                    <div class="absolute bottom-4 left-4 right-4 lg:hidden text-white text-xs">
                        <span class="font-bold block text-sm">Gerai Pusat & Fasilitas Distribusi</span>
                        <span>PT Urwah Barokah Sukses — DONS MEAT Indonesia</span>
                    </div>
                </div>

                <!-- Content -->
                <div class="lg:col-span-5 p-8 sm:p-10 text-white space-y-5">
                    <span class="text-xs font-bold uppercase tracking-widest text-red-400 block">
                        Fasilitas & Standar Operasional
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white leading-snug">
                        Standar Rantai Dingin & Distribusi Berpengalaman
                    </h2>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Kualitas daging sangat ditentukan oleh suhu penanganan. DONS MEAT menerapkan sistem <em>cold-chain management</em> terpadu sejak penerimaan, penyimpanan di <em>cold storage</em> bersuhu teratur, pemotongan higienis, hingga pengantaran menggunakan armada berpendingin khusus.
                    </p>
                    
                    <div class="pt-2 border-t border-slate-800 grid grid-cols-2 gap-4 text-xs">
                        <div class="space-y-1">
                            <span class="font-bold text-amber-300 block text-base sm:text-lg">Cold Storage</span>
                            <span class="text-slate-400">Penyimpanan suhu beku optimal menjaga nutrisi alami.</span>
                        </div>
                        <div class="space-y-1">
                            <span class="font-bold text-amber-300 block text-base sm:text-lg">Armada Khusus</span>
                            <span class="text-slate-400">Pengiriman terstandarisasi menjaga rantai beku sampai tujuan.</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 3. Pilar Mutu & Legalitas (Halal, NKV, Higienis) -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12 space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-red-700">Sertifikasi & Jaminan</span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900">3 Pilar Jaminan Mutu DONS MEAT</h2>
            <p class="text-sm text-slate-600">Keamanan pangan dan kepatuhan syariat adalah komitmen mutlak yang selalu kami jaga.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Halal -->
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-md transition space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-black text-2xl shadow-xs">
                    🕌
                </div>
                <div class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Sertifikasi Halal Indonesia
                </div>
                <h3 class="text-xl font-bold text-slate-900">100% Halal Terjamin</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Setiap hewan potong disembelih dan ditangani sesuai syariat Islam yang ketat, dibekali sertifikasi Halal resmi BPJPH Indonesia demi ketenteraman konsumsi keluarga Anda.
                </p>
            </div>

            <!-- NKV -->
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-md transition space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-red-100 text-red-800 flex items-center justify-center font-black text-2xl shadow-xs">
                    🛡️
                </div>
                <div class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">
                    Sertifikasi NKV Resmi
                </div>
                <h3 class="text-xl font-bold text-slate-900">Standar NKV Veteriner</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Memiliki Nomor Kontrol Veteriner (NKV) yang membuktikan bahwa fasilitas, sanitasi, dan proses penyimpanan daging telah lulus audit teknis higienitas dari dinas peternakan.
                </p>
            </div>

            <!-- Vacuum Sealed -->
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-md transition space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-800 flex items-center justify-center font-black text-2xl shadow-xs">
                    ❄️
                </div>
                <div class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    Vacuum Sealed Packaging
                </div>
                <h3 class="text-xl font-bold text-slate-900">Kemasan Kedap Udara 500g</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Dikemas vakum rapi dan higienis per 500 gram (*ready-to-cook*). Mengunci kelembapan, bebas bakteri luar, memperpanjang daya simpan, dan siap langsung diolah.
                </p>
            </div>
        </div>
    </section>

    <!-- 4. Showcase Lini Kemasan Ritel Resmi (5 Poster Produk) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 rounded-3xl p-8 sm:p-12 text-white border border-slate-800 shadow-2xl space-y-12">
            
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <div class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-red-700/80 text-red-100 border border-red-600/50">
                    Koleksi Produk Ritel Resmi
                </div>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-white">
                    Lini Kemasan Siap Olah dari DONS MEAT
                </h2>
                <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                    Produk daging pilihan yang dikemas praktis 500 gram dengan standar higienis tinggi, memudahkan ibu rumah tangga dan chef menyajikan masakan istimewa tanpa repot.
                    <span class="block text-xs text-red-300 mt-1 font-semibold">Klik pada foto poster untuk memperbesar tampilan kemasan resmi.</span>
                </p>
            </div>

            <!-- Posters Grid (5 items) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">

                <!-- Poster 1: IGA PREMIUM -->
                <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden hover:border-red-500/50 transition duration-300 flex flex-col group">
                    <div class="relative aspect-9/16 max-h-[380px] bg-black/40 overflow-hidden cursor-pointer"
                         onclick="openPosterModal('{{ asset('images/about/iga-premium.jpg') }}', 'Iga Premium — DONS MEAT')">
                        <img src="{{ asset('images/about/iga-premium.jpg') }}" 
                             alt="Poster Kemasan Iga Premium DONS MEAT" 
                             class="w-full h-full object-cover object-top group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/0 transition"></div>
                        <span class="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-black/70 text-white backdrop-blur-xs flex items-center space-x-1">
                            <span>🔍 Klik Perbesar</span>
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[11px] font-bold text-amber-300 uppercase tracking-wide">Kemasan 500 gr • NKV & Halal</span>
                            <h3 class="text-lg font-bold text-white mt-0.5">Iga Premium Sapi Lokal</h3>
                            <p class="text-xs text-slate-300 leading-relaxed mt-1">
                                Iga sapi lokal pilihan berdaging tebal, gurih, dan bertekstur lembut.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-white/10">
                            <span class="text-[11px] font-semibold text-red-300 block mb-1">Rekomendasi Menu:</span>
                            <div class="flex flex-wrap gap-1.5 text-[11px] text-slate-200">
                                <span class="px-2 py-0.5 rounded bg-white/10">Iga Bakar</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Sop Iga</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Iga Goreng</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Kuah Rempah</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Poster 2: SAIKORO MELTIQ -->
                <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden hover:border-red-500/50 transition duration-300 flex flex-col group">
                    <div class="relative aspect-9/16 max-h-[380px] bg-black/40 overflow-hidden cursor-pointer"
                         onclick="openPosterModal('{{ asset('images/about/saikoro-meltiq.jpg') }}', 'Saikoro Meltiq — DONS MEAT')">
                        <img src="{{ asset('images/about/saikoro-meltiq.jpg') }}" 
                             alt="Poster Kemasan Saikoro Meltiq DONS MEAT" 
                             class="w-full h-full object-cover object-top group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/0 transition"></div>
                        <span class="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-black/70 text-white backdrop-blur-xs flex items-center space-x-1">
                            <span>🔍 Klik Perbesar</span>
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[11px] font-bold text-amber-300 uppercase tracking-wide">Kemasan 500 gr • Potongan Dadu</span>
                            <h3 class="text-lg font-bold text-white mt-0.5">Saikoro Meltiq Beef</h3>
                            <p class="text-xs text-slate-300 leading-relaxed mt-1">
                                Potongan dadu praktis bertekstur empuk, lembut, dan juicy lumer di lidah.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-white/10">
                            <span class="text-[11px] font-semibold text-red-300 block mb-1">Rekomendasi Menu:</span>
                            <div class="flex flex-wrap gap-1.5 text-[11px] text-slate-200">
                                <span class="px-2 py-0.5 rounded bg-white/10">Steak Dadu</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Tumis Saikoro</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Yakiniku</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">BBQ</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Poster 3: SLICE BRISKET PREMIUM -->
                <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden hover:border-red-500/50 transition duration-300 flex flex-col group">
                    <div class="relative aspect-9/16 max-h-[380px] bg-black/40 overflow-hidden cursor-pointer"
                         onclick="openPosterModal('{{ asset('images/about/slice-brisket.jpg') }}', 'Slice Brisket Premium — DONS MEAT')">
                        <img src="{{ asset('images/about/slice-brisket.jpg') }}" 
                             alt="Poster Kemasan Slice Brisket Premium DONS MEAT" 
                             class="w-full h-full object-cover object-top group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/0 transition"></div>
                        <span class="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-black/70 text-white backdrop-blur-xs flex items-center space-x-1">
                            <span>🔍 Klik Perbesar</span>
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[11px] font-bold text-amber-300 uppercase tracking-wide">Netto 500 gr • Iris Tipis</span>
                            <h3 class="text-lg font-bold text-white mt-0.5">Slice Brisket Premium</h3>
                            <p class="text-xs text-slate-300 leading-relaxed mt-1">
                                Irisan brisket sapi berkualitas dengan lapisan daging dan lemak yang seimbang.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-white/10">
                            <span class="text-[11px] font-semibold text-red-300 block mb-1">Rekomendasi Menu:</span>
                            <div class="flex flex-wrap gap-1.5 text-[11px] text-slate-200">
                                <span class="px-2 py-0.5 rounded bg-white/10">Shabu-shabu</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">BBQ / Grill</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Sukiyaki</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Beef Bowl</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Poster 4: RAWONAN DAGING SAPI -->
                <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden hover:border-red-500/50 transition duration-300 flex flex-col group">
                    <div class="relative aspect-9/16 max-h-[380px] bg-black/40 overflow-hidden cursor-pointer"
                         onclick="openPosterModal('{{ asset('images/about/rawonan.jpg') }}', 'Rawonan Daging Sapi — DONS MEAT')">
                        <img src="{{ asset('images/about/rawonan.jpg') }}" 
                             alt="Poster Kemasan Rawonan DONS MEAT" 
                             class="w-full h-full object-cover object-top group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/0 transition"></div>
                        <span class="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-black/70 text-white backdrop-blur-xs flex items-center space-x-1">
                            <span>🔍 Klik Perbesar</span>
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[11px] font-bold text-amber-300 uppercase tracking-wide">Berat Bersih 500 gr • Siap Masak</span>
                            <h3 class="text-lg font-bold text-white mt-0.5">Rawonan Sapi Lokal</h3>
                            <p class="text-xs text-slate-300 leading-relaxed mt-1">
                                Potongan daging sapi ukuran dadu praktis, tahan empuk dimasak lama berkuah.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-white/10">
                            <span class="text-[11px] font-semibold text-red-300 block mb-1">Rekomendasi Menu:</span>
                            <div class="flex flex-wrap gap-1.5 text-[11px] text-slate-200">
                                <span class="px-2 py-0.5 rounded bg-white/10">Rawon Jawa Timur</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Sop Daging</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Semur</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Tumis Pedas</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Poster 5: IGA GONRONG -->
                <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden hover:border-red-500/50 transition duration-300 flex flex-col group">
                    <div class="relative aspect-9/16 max-h-[380px] bg-black/40 overflow-hidden cursor-pointer"
                         onclick="openPosterModal('{{ asset('images/about/iga-gonrong.jpg') }}', 'Iga Gonrong — DONS MEAT')">
                        <img src="{{ asset('images/about/iga-gonrong.jpg') }}" 
                             alt="Poster Kemasan Iga Gonrong DONS MEAT" 
                             class="w-full h-full object-cover object-top group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/0 transition"></div>
                        <span class="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-black/70 text-white backdrop-blur-xs flex items-center space-x-1">
                            <span>🔍 Klik Perbesar</span>
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[11px] font-bold text-amber-300 uppercase tracking-wide">Kemasan 500 gr • Daging Ekstra Tebal</span>
                            <h3 class="text-lg font-bold text-white mt-0.5">Iga Gonrong Spesial</h3>
                            <p class="text-xs text-slate-300 leading-relaxed mt-1">
                                Potongan iga sapi dengan bagian daging yang lebih panjang dan tebal (gonrong).
                            </p>
                        </div>
                        <div class="pt-2 border-t border-white/10">
                            <span class="text-[11px] font-semibold text-red-300 block mb-1">Rekomendasi Menu:</span>
                            <div class="flex flex-wrap gap-1.5 text-[11px] text-slate-200">
                                <span class="px-2 py-0.5 rounded bg-white/10">Iga Konro Bakar</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Sop Konro</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Iga Kuah Rempah</span>
                                <span class="px-2 py-0.5 rounded bg-white/10">Iga Balado</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 6: Info Hubungi Toko untuk Pesanan Kemasan -->
                <div class="bg-red-950/60 border border-red-800/60 rounded-2xl p-6 sm:p-8 flex flex-col justify-between space-y-6">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-xl bg-red-600/30 text-amber-300 flex items-center justify-center font-bold text-xl">
                            📦
                        </div>
                        <h3 class="text-xl font-extrabold text-white">Butuh Stok Kemasan Retail / Grosir?</h3>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                            Kami melayani pembelian satuan untuk kebutuhan rumah tangga hingga pasokan teratur dalam jumlah besar untuk warung, restoran, hotel, dan katering dengan harga khusus.
                        </p>
                    </div>
                    <div class="space-y-3 pt-4 border-t border-red-900/40">
                        <a href="{{ route('catalog.index') }}" 
                           class="block w-full py-3 px-4 rounded-xl text-center text-xs font-bold text-white bg-red-700 hover:bg-red-600 transition shadow-lg">
                            Buka Katalog Daging &rarr;
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $setting->phone ?? '') }}" 
                           target="_blank"
                           class="block w-full py-3 px-4 rounded-xl text-center text-xs font-semibold text-slate-200 bg-white/10 hover:bg-white/20 transition">
                            Konsultasi via WhatsApp
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 5. Segmen Layanan: Rumah Tangga & Horeca -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <div class="bg-white p-8 sm:p-10 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-800 flex items-center justify-center font-black text-xl">
                    🏠
                </div>
                <h3 class="text-2xl font-bold text-slate-900">Solusi Dapur Rumah Tangga</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Tidak perlu repot ke pasar tradisional di pagi hari atau bingung memotong daging sendiri. DONS MEAT menyediakan aneka daging dengan takaran berat praktis (250g, 500g, 1kg) yang higienis, bersih, dan siap langsung dimasak untuk keluarga tercinta.
                </p>
                <ul class="text-xs text-slate-600 space-y-2 pt-2">
                    <li class="flex items-center space-x-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Potongan presisi, bersih tanpa kotoran atau bau asing.</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Bisa ambil langsung di toko atau diantar ke depan pintu rumah.</span>
                    </li>
                </ul>
            </div>

            <div class="bg-white p-8 sm:p-10 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-800 flex items-center justify-center font-black text-xl">
                    🍳
                </div>
                <h3 class="text-2xl font-bold text-slate-900">Mitra Usaha Kuliner & Horeca</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Kami menjadi mitra terpercaya bagi puluhan pengusaha kuliner, katering hajatan, rumah makan, dan restoran steak/BBQ. Kami menjamin kestabilan pasokan, potongan spesifikasi khusus yang seragam, serta harga grosir yang kompetitif.
                </p>
                <ul class="text-xs text-slate-600 space-y-2 pt-2">
                    <li class="flex items-center space-x-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Kontrak pasokan berkala dengan jaminan ketersediaan stok.</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Fasilitas invoice resmi & pengiriman terjadwal setiap hari.</span>
                    </li>
                </ul>
            </div>

        </div>
    </section>

    <!-- 6. Official Standard Price Feature -->
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-br from-red-900 to-slate-900 rounded-3xl p-8 sm:p-10 text-white shadow-xl text-center space-y-4">
            <span class="text-xs font-bold uppercase tracking-widest text-red-300">
                Transparansi Bisnis
            </span>
            <h2 class="text-xl sm:text-3xl font-extrabold">Harga Acuan Standar Daging Resmi</h2>
            <div class="text-4xl sm:text-5xl font-black text-amber-300 font-mono tracking-tight">
                Rp {{ number_format($setting->standard_beef_price_per_kg, 0, ',', '.') }} <span class="text-lg font-normal text-slate-300">/ kg</span>
            </div>
            <p class="text-xs sm:text-sm text-red-200/80 max-w-lg mx-auto leading-relaxed">
                DONS MEAT berkomitmen menjaga transparansi harga bagi seluruh pelanggan. Harga acuan ini berlaku harian dan dapat disesuaikan manajemen berdasarkan dinamika pasar pasokan ternak lokal.
            </p>
        </div>
    </section>

    <!-- 7. Detail Operasional & Kontak Resmi -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10 space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-red-700">Kunjungi Gerai Kami</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Informasi Lokasi & Kontak</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                <div class="w-10 h-10 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-lg">
                    📍
                </div>
                <h3 class="font-bold text-base text-slate-900">Alamat Usaha</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    {{ $setting->address ?? 'Jl. Pengayoman, Makassar, Sulawesi Selatan' }}
                </p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                <div class="w-10 h-10 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-lg">
                    ⏰
                </div>
                <h3 class="font-bold text-base text-slate-900">Jam Operasional</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                    {{ $setting->opening_hours ?? "Senin - Sabtu: 08:00 - 18:00 WITA\nMinggu: 08:00 - 15:00 WITA" }}
                </p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                <div class="w-10 h-10 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-lg">
                    📞
                </div>
                <h3 class="font-bold text-base text-slate-900">Kontak Resmi</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    WhatsApp: <span class="font-bold text-slate-800">{{ $setting->phone ?? '08123456789' }}</span><br>
                    @if($setting->email)
                        Email: <span class="font-bold text-slate-800">{{ $setting->email }}</span>
                    @endif
                </p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                <div class="w-10 h-10 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-lg">
                    🚚
                </div>
                <h3 class="font-bold text-base text-slate-900">Pengiriman & Ambil</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    • <strong>Ambil di Toko:</strong> Gratis & terjadwal.<br>
                    • <strong>Kurir Toko / Instant:</strong> Siap dikirim segar.
                </p>
            </div>

        </div>

        <div class="text-center pt-10">
            <a href="{{ route('catalog.index') }}" 
               class="inline-flex items-center px-8 py-4 rounded-xl bg-gradient-to-r from-red-700 to-red-800 hover:from-red-600 hover:to-red-700 text-white font-extrabold text-sm shadow-xl hover:shadow-red-700/40 transition transform hover:-translate-y-0.5">
                Jelajahi Seluruh Katalog & Mulai Pesan
            </a>
        </div>
    </section>

</div>

<!-- Lightbox Modal for Poster Zoom -->
<div id="posterModal" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
    <div class="relative max-w-3xl w-full max-h-[90vh] flex flex-col items-center">
        <!-- Close Button -->
        <button type="button" 
                onclick="closePosterModal()"
                class="absolute -top-12 right-0 sm:-right-4 text-white hover:text-red-400 p-2 text-2xl font-black focus:outline-none transition">
            ✕ <span class="text-xs font-normal">Tutup</span>
        </button>
        
        <img id="posterModalImg" 
             src="" 
             alt="Poster Preview" 
             class="max-w-full max-h-[82vh] object-contain rounded-2xl shadow-2xl border border-white/20">
        
        <div id="posterModalCaption" class="mt-3 text-center text-sm font-bold text-white tracking-wide"></div>
    </div>
</div>

<script>
    function openPosterModal(imageSrc, caption) {
        const modal = document.getElementById('posterModal');
        const img = document.getElementById('posterModalImg');
        const cap = document.getElementById('posterModalCaption');
        
        img.src = imageSrc;
        cap.textContent = caption;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closePosterModal() {
        const modal = document.getElementById('posterModal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close on click outside or ESC
    document.getElementById('posterModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closePosterModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePosterModal();
        }
    });
</script>
@endsection
