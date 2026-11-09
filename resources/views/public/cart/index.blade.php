@extends('layouts.public')

@section('title', 'Keranjang Belanja - DONS MEAT')

@section('content')
<div class="py-8 sm:py-12 bg-slate-50 min-h-[75vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb & Header -->
        <div class="mb-6">
            <nav class="flex items-center space-x-2 text-xs text-slate-500 mb-2">
                <a href="{{ route('home') }}" class="hover:text-red-700">Beranda</a>
                <span>/</span>
                <a href="{{ route('catalog.index') }}" class="hover:text-red-700">Katalog</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Keranjang Belanja</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Keranjang Belanja Daging</h1>
            <p class="text-sm text-slate-600 mt-1">Periksa potongan daging dan pilihan berat sebelum melanjutkan ke checkout.</p>
        </div>

        @if(session('status'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($cart['items']->isEmpty())
            <!-- Empty Cart State -->
            <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-16 text-center max-w-2xl mx-auto shadow-sm">
                <div class="w-20 h-20 mx-auto rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900">Keranjang Belanja Anda Kosong</h2>
                <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto">
                    Anda belum memilih potongan daging segar. Jelajahi katalog kami untuk menemukan aneka daging sapi, kambing, ayam, potongan steak, atau tulang berkualitas.
                </p>
                <div class="mt-6">
                    <a href="{{ route('catalog.index') }}" 
                       class="inline-flex items-center px-6 py-3 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow transition">
                        <span>Pilih Daging Sekarang</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        @else
            <!-- Cart Grid: 2 Columns on desktop -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left: Items List (8 cols) -->
                <div class="lg:col-span-8 space-y-4">
                    @if($cart['has_unavailable'])
                        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <span class="font-bold block">Perhatian: Stok atau ketersediaan produk berubah</span>
                                <span>Beberapa item di keranjang memiliki jumlah yang melebihi stok siap jual atau tidak aktif. Harap sesuaikan kuantitas sebelum melanjutkan checkout.</span>
                            </div>
                        </div>
                    @endif

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
                        @foreach($cart['items'] as $item)
                            <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 {{ ! $item->is_stock_sufficient || ! $item->is_active ? 'bg-red-50/40' : '' }}">
                                <!-- Product details -->
                                <div class="flex items-center gap-4 min-w-0 flex-1">
                                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                        @if($item->product->image_path)
                                            <img src="{{ asset('storage/' . $item->product->image_path) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-xs font-bold text-slate-400">{{ substr($item->product->name, 0, 2) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-[11px] font-semibold text-red-700 uppercase tracking-wider">
                                            {{ $item->product->category->name ?? 'Daging Sapi' }}
                                        </div>
                                        <h3 class="font-bold text-slate-900 text-sm sm:text-base truncate">
                                            <a href="{{ route('catalog.show', $item->product->slug) }}" class="hover:text-red-700 transition">
                                                {{ $item->product->name }}
                                            </a>
                                        </h3>
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            Pilihan: <strong class="text-slate-700">{{ $item->weight_option->label }}</strong>
                                            <span class="mx-1">•</span>
                                            Harga acuan: <span class="font-mono">Rp {{ number_format($item->price_per_kg, 0, ',', '.') }}/kg</span>
                                        </div>

                                        @if(! $item->is_active)
                                            <span class="inline-block mt-1 text-[11px] font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded">
                                                Produk / Opsi Tidak Aktif
                                            </span>
                                        @elseif(! $item->is_stock_sufficient)
                                            <span class="inline-block mt-1 text-[11px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded">
                                                Stok Tersedia: {{ number_format($item->available_stock_gram) }} g (Kurang)
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Stepper, Subtotal & Remove -->
                                <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                    <!-- Stepper Form -->
                                    <div class="flex items-center border border-slate-300 rounded-xl bg-slate-50 overflow-hidden">
                                        <form method="POST" action="{{ route('cart.update', $item->weight_option->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ $item->quantity - 1 }}">
                                            <button type="submit" class="px-3 py-1 text-slate-600 hover:bg-slate-200 text-sm font-bold" title="Kurangi">
                                                -
                                            </button>
                                        </form>

                                        <span class="px-2 text-xs font-mono font-bold text-slate-800">{{ $item->quantity }}</span>

                                        <form method="POST" action="{{ route('cart.update', $item->weight_option->id) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                            <button type="submit" class="px-3 py-1 text-slate-600 hover:bg-slate-200 text-sm font-bold" title="Tambah">
                                                +
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Price Subtotal -->
                                    <div class="text-right min-w-[100px]">
                                        <span class="text-xs text-slate-400 block sm:hidden">Subtotal:</span>
                                        <span class="font-extrabold text-slate-900 font-mono text-sm sm:text-base">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 block font-mono">
                                            {{ number_format($item->line_weight_gram) }} g
                                        </span>
                                    </div>

                                    <!-- Delete Item -->
                                    <form method="POST" action="{{ route('cart.destroy', $item->weight_option->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-red-600 p-1.5 transition" title="Hapus item">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('catalog.index') }}" class="text-xs font-semibold text-red-700 hover:text-red-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Lanjut Belanja Daging Lainnya
                        </a>

                        <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Kosongkan semua isi keranjang?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-slate-400 hover:text-red-600 transition">
                                Kosongkan Keranjang
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Summary Card (4 cols) -->
                <div class="lg:col-span-4">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sticky top-24 space-y-5">
                        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">
                            Ringkasan Pesanan
                        </h2>

                        <div class="space-y-2.5 text-xs text-slate-600">
                            <div class="flex justify-between">
                                <span>Total Paket</span>
                                <span class="font-bold text-slate-800 font-mono">{{ $cart['total_quantity'] }} paket</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total Berat Bersih</span>
                                <span class="font-bold text-slate-800 font-mono">{{ number_format($cart['total_weight_gram']) }} g ({{ number_format($cart['total_weight_gram'] / 1000, 2) }} kg)</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Subtotal Belanja</span>
                                <span class="font-bold text-slate-900 font-mono text-sm">Rp {{ number_format($cart['subtotal'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-slate-400 pt-1">
                                <span>Ongkos Kirim</span>
                                <span>Dihitung di Checkout</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                            <div>
                                <span class="text-xs text-slate-500 font-medium block">Total Sementara</span>
                                <span class="text-xs text-slate-400">(Belum termasuk ongkir)</span>
                            </div>
                            <div class="text-xl font-extrabold text-red-900 font-mono">
                                Rp {{ number_format($cart['subtotal'], 0, ',', '.') }}
                            </div>
                        </div>

                        @if($cart['has_unavailable'])
                            <button type="button" disabled class="w-full py-3.5 px-4 rounded-xl bg-slate-200 text-slate-400 font-bold text-sm cursor-not-allowed">
                                Stok Tidak Cukup / Tidak Aktif
                            </button>
                        @else
                            <a href="{{ route('checkout.index') }}" 
                               class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-red-700 to-red-800 hover:from-red-800 hover:to-red-900 text-white font-extrabold text-sm text-center shadow transition block">
                                Lanjut ke Checkout Tamu
                            </a>
                        @endif

                        <div class="p-3 bg-slate-50 rounded-xl text-[11px] text-slate-500 space-y-1">
                            <p class="font-semibold text-slate-700">🔒 Checkout Cepat & Aman:</p>
                            <p>• Tidak wajib membuat akun atau mendaftar password.</p>
                            <p>• Stok dicadangkan otomatis saat Anda membuat pesanan.</p>
                            <p>• Pembayaran via Midtrans Snap (QRIS, Transfer Bank, E-Wallet).</p>
                        </div>
                    </div>
                </div>

            </div>
        @endif
    </div>
</div>
@endsection
