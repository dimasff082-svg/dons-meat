@extends('layouts.public')

@section('title', 'Checkout Pesanan - DONS MEAT')

@section('content')
<div class="py-8 sm:py-12 bg-slate-50 min-h-[80vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb & Header -->
        <div class="mb-6">
            <nav class="flex items-center space-x-2 text-xs text-slate-500 mb-2">
                <a href="{{ route('home') }}" class="hover:text-red-700">Beranda</a>
                <span>/</span>
                <a href="{{ route('cart.index') }}" class="hover:text-red-700">Keranjang</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Checkout Tamu</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Checkout Pesanan Daging</h1>
            <p class="text-sm text-slate-600 mt-1">Lengkapi data penerima dan metode pemenuhan pesanan tanpa perlu registrasi akun.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                <strong class="font-bold">Mohon periksa formulir pesanan Anda:</strong>
                <ul class="list-disc list-inside mt-1.5 space-y-0.5 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left: Customer Information & Fulfillment (7 cols) -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- 1. Customer Contact Details -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs flex items-center justify-center">1</span>
                            <h2 class="text-base font-bold text-slate-900">Informasi Pemesan</h2>
                        </div>

                        <div>
                            <label for="customer_name" class="block text-xs font-bold text-slate-700 mb-1">
                                Nama Lengkap <span class="text-red-600">*</span>
                            </label>
                            <input type="text" 
                                   name="customer_name" 
                                   id="customer_name" 
                                   value="{{ old('customer_name') }}" 
                                   placeholder="Contoh: Budi Santoso"
                                   required 
                                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border @error('customer_name') border-red-500 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-red-500">
                            @error('customer_name')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="customer_phone" class="block text-xs font-bold text-slate-700 mb-1">
                                    No. Handphone / WhatsApp <span class="text-red-600">*</span>
                                </label>
                                <input type="tel" 
                                       name="customer_phone" 
                                       id="customer_phone" 
                                       value="{{ old('customer_phone') }}" 
                                       placeholder="Contoh: 081234567890"
                                       required 
                                       class="w-full px-3.5 py-2.5 text-sm rounded-xl border @error('customer_phone') border-red-500 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="text-[11px] text-slate-400 mt-1 block">Untuk konfirmasi pesanan & koordinasi kurir</span>
                                @error('customer_phone')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="customer_email" class="block text-xs font-bold text-slate-700 mb-1">
                                    Alamat Email (Opsional)
                                </label>
                                <input type="email" 
                                       name="customer_email" 
                                       id="customer_email" 
                                       value="{{ old('customer_email') }}" 
                                       placeholder="nama@email.com"
                                       class="w-full px-3.5 py-2.5 text-sm rounded-xl border @error('customer_email') border-red-500 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span class="text-[11px] text-slate-400 mt-1 block">Bukti transaksi dikirim ke email jika diisi</span>
                                @error('customer_email')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 2. Fulfillment Method -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs flex items-center justify-center">2</span>
                            <h2 class="text-base font-bold text-slate-900">Metode Pengambilan Daging</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Option: Pickup -->
                            <label class="relative flex items-start p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50/40 transition">
                                <input type="radio" 
                                       name="fulfillment_method" 
                                       value="pickup" 
                                       class="mt-1 text-red-600 focus:ring-red-500" 
                                       {{ old('fulfillment_method', 'pickup') === 'pickup' ? 'checked' : '' }}
                                       onchange="toggleFulfillmentFields()">
                                <div class="ml-3 text-xs">
                                    <span class="font-bold text-slate-900 text-sm block">Ambil di Toko (Pickup)</span>
                                    <span class="text-emerald-700 font-bold font-mono">Gratis Ongkir (Rp 0)</span>
                                    <span class="text-slate-500 block mt-1">Ambil langsung pesanan Anda di gerai DONS MEAT.</span>
                                </div>
                            </label>

                            <!-- Option: Delivery -->
                            <label class="relative flex items-start p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50/40 transition">
                                <input type="radio" 
                                       name="fulfillment_method" 
                                       value="delivery" 
                                       class="mt-1 text-red-600 focus:ring-red-500" 
                                       {{ old('fulfillment_method') === 'delivery' ? 'checked' : '' }}
                                       onchange="toggleFulfillmentFields()">
                                <div class="ml-3 text-xs">
                                    <span class="font-bold text-slate-900 text-sm block">Kirim ke Alamat (Delivery)</span>
                                    <span class="text-slate-700 font-semibold">Kurir Toko / Instan</span>
                                    <span class="text-slate-500 block mt-1">Daging diantar dengan kemasan dingin higienis.</span>
                                </div>
                            </label>
                        </div>

                        <!-- Pickup Store Address Info -->
                        <div id="pickupInfoSection" class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1.5">
                            <div class="flex items-center gap-1.5 font-bold text-slate-800">
                                <svg class="w-4 h-4 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Lokasi Pengambilan:
                            </div>
                            <p class="font-semibold text-slate-800">{{ $storeSetting->business_name }} — {{ $storeSetting->address ?? 'Jl. Pengayoman, Makassar' }}</p>
                            <p class="text-slate-500 whitespace-pre-line">Jam Operasional: {{ $storeSetting->opening_hours }}</p>
                            <p class="text-[11px] text-slate-400">Pesanan dapat diambil setelah status pesanan berubah menjadi "Siap Diambil".</p>
                        </div>

                        <!-- Delivery Address & Zone Input -->
                        <div id="deliveryFieldsSection" class="space-y-4 pt-2 hidden">
                            <div>
                                <label for="delivery_zone_id" class="block text-xs font-bold text-slate-700 mb-1">
                                    Pilih Zona Wilayah Pengiriman <span class="text-red-600">*</span>
                                </label>
                                <select name="delivery_zone_id" 
                                        id="delivery_zone_id" 
                                        onchange="updateDeliveryFee()"
                                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border @error('delivery_zone_id') border-red-500 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="" data-fee="0">-- Pilih Zona Pengantaran --</option>
                                    @foreach($deliveryZones as $zone)
                                        <option value="{{ $zone->id }}" 
                                                data-fee="{{ $zone->fee }}" 
                                                data-min="{{ $zone->minimum_order_amount ?? 0 }}"
                                                {{ old('delivery_zone_id') == $zone->id ? 'selected' : '' }}>
                                            {{ $zone->name }} — Ongkir: Rp {{ number_format($zone->fee, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('delivery_zone_id')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="delivery_address" class="block text-xs font-bold text-slate-700 mb-1">
                                    Alamat Pengantaran Lengkap <span class="text-red-600">*</span>
                                </label>
                                <textarea name="delivery_address" 
                                          id="delivery_address" 
                                          rows="3" 
                                          placeholder="Nama jalan, nomor rumah/ruko, RT/RW, kelurahan, dan patokan alamat..."
                                          class="w-full px-3.5 py-2 text-sm rounded-xl border @error('delivery_address') border-red-500 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('delivery_address') }}</textarea>
                                @error('delivery_address')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Customer Note -->
                        <div class="pt-2">
                            <label for="customer_note" class="block text-xs font-bold text-slate-700 mb-1">
                                Catatan Khusus untuk Toko (Opsional)
                            </label>
                            <input type="text" 
                                   name="customer_note" 
                                   id="customer_note" 
                                   value="{{ old('customer_note') }}" 
                                   placeholder="Contoh: Titip di pos satpam / Minta dipotong dadu sedang"
                                   maxlength="255"
                                   class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>

                    <!-- 3. Payment Method Choice (COD vs Midtrans) -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs flex items-center justify-center">3</span>
                            <h2 class="text-base font-bold text-slate-900">Pilih Metode Pembayaran</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Option: Midtrans Gateway -->
                            <label class="relative flex items-start p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50/40 transition">
                                <input type="radio" 
                                       name="payment_method" 
                                       id="paymentMethodMidtrans"
                                       value="midtrans" 
                                       class="mt-1 text-red-600 focus:ring-red-500" 
                                       {{ old('payment_method', 'midtrans') === 'midtrans' ? 'checked' : '' }}
                                       onchange="togglePaymentMethodInfo()">
                                <div class="ml-3 text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-900 text-sm block">Payment Gateway</span>
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Midtrans</span>
                                    </div>
                                    <span class="text-slate-600 font-medium block mt-1">QRIS, E-Wallet (GoPay/ShopeePay), Virtual Account BCA, BRI, BNI, Mandiri</span>
                                    <span class="text-slate-400 block mt-1">Verifikasi pembayaran online otomatis & instan.</span>
                                </div>
                            </label>

                            <!-- Option: Cash on Delivery (COD) -->
                            <label class="relative flex items-start p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50/40 transition">
                                <input type="radio" 
                                       name="payment_method" 
                                       id="paymentMethodCod"
                                       value="cod" 
                                       class="mt-1 text-red-600 focus:ring-red-500" 
                                       {{ old('payment_method') === 'cod' ? 'checked' : '' }}
                                       onchange="togglePaymentMethodInfo()">
                                <div class="ml-3 text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-900 text-sm block">Bayar di Tempat (COD)</span>
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Tunai</span>
                                    </div>
                                    <span class="text-slate-600 font-medium block mt-1">Bayar tunai kepada kurir atau kasir saat menerima daging</span>
                                    <span class="text-slate-400 block mt-1">Pesanan langsung dipersiapkan dan diantar.</span>
                                </div>
                            </label>
                        </div>

                        @error('payment_method')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror

                        <div id="paymentMidtransNotice" class="p-3.5 rounded-xl bg-blue-50/60 border border-blue-200 text-xs text-blue-900 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <strong>Metode Pembayaran Online:</strong> Setelah tombol konfirmasi ditekan, pop-up pembayaran Midtrans Snap akan terbuka dengan kode QRIS dan nomor Virtual Account.
                            </div>
                        </div>

                        <div id="paymentCodNotice" class="hidden p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-200 text-xs text-emerald-900 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-emerald-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <strong>Metode Bayar di Tempat:</strong> Pesanan Anda segera disiapkan tanpa menunggu transfer. Mohon siapkan uang tunai pas saat kurir mengantarkan daging.
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right: Order Summary & Payment Button (5 cols) -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sticky top-24 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h2 class="text-base font-bold text-slate-900">Rincian Belanja Daging</h2>
                            <a href="{{ route('cart.index') }}" class="text-xs text-red-700 hover:underline font-semibold">Ubah</a>
                        </div>

                        <!-- Mini Items List -->
                        <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 pr-1 text-xs">
                            @foreach($cart['items'] as $item)
                                <div class="py-2.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-slate-800 truncate">{{ $item->product->name }}</div>
                                        <div class="text-[11px] text-slate-500">
                                            {{ $item->weight_option->label }} x {{ $item->quantity }} paket
                                        </div>
                                    </div>
                                    <div class="text-right font-mono font-bold text-slate-900">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pricing Breakdown -->
                        <div class="pt-3 border-t border-slate-100 space-y-2 text-xs">
                            <div class="flex justify-between text-slate-600">
                                <span>Total Berat Daging</span>
                                <span class="font-mono font-semibold">{{ number_format($cart['total_weight_gram']) }} g ({{ number_format($cart['total_weight_gram'] / 1000, 2) }} kg)</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal Produk</span>
                                <span class="font-mono font-semibold" id="cartSubtotalText" data-subtotal="{{ $cart['subtotal'] }}">
                                    Rp {{ number_format($cart['subtotal'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Ongkos Kirim</span>
                                <span class="font-mono font-semibold text-slate-800" id="deliveryFeeDisplay">
                                    Rp 0
                                </span>
                            </div>
                            <div class="pt-3 border-t border-slate-200 flex justify-between items-center text-slate-900">
                                <div>
                                    <span class="text-sm font-bold block">Total Pembayaran</span>
                                    <span class="text-[11px] text-slate-400">Harga resmi terverifikasi server</span>
                                </div>
                                <div class="text-2xl font-black text-red-900 font-mono" id="grandTotalDisplay">
                                    Rp {{ number_format($cart['subtotal'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <!-- Submit CTA -->
                        <div class="pt-2">
                            <button type="submit" 
                                    id="submitCheckoutBtn"
                                    class="w-full py-4 px-6 rounded-xl bg-gradient-to-r from-red-700 to-red-800 hover:from-red-800 hover:to-red-900 text-white font-extrabold text-base shadow-lg transition flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Konfirmasi & Buat Pesanan</span>
                            </button>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-xl text-[11px] text-slate-500 space-y-1">
                            <p class="font-semibold text-slate-700">Ketentuan Pemesanan:</p>
                            <p>• Daging sapi dipotong dan dikemas higienis sesuai standar DONS MEAT.</p>
                            <p>• Stok dicadangkan otomatis selama {{ $storeSetting->payment_expiry_minutes }} menit.</p>
                            <p>• Setelah menekan tombol konfirmasi, Anda akan diarahkan ke halaman pelacakan & pembayaran.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleFulfillmentFields() {
        const method = document.querySelector('input[name="fulfillment_method"]:checked')?.value || 'pickup';
        const pickupInfo = document.getElementById('pickupInfoSection');
        const deliveryFields = document.getElementById('deliveryFieldsSection');
        const deliveryZoneSelect = document.getElementById('delivery_zone_id');
        const deliveryAddressTextarea = document.getElementById('delivery_address');

        if (method === 'delivery') {
            pickupInfo.classList.add('hidden');
            deliveryFields.classList.remove('hidden');
            deliveryZoneSelect.setAttribute('required', 'required');
            deliveryAddressTextarea.setAttribute('required', 'required');
        } else {
            pickupInfo.classList.remove('hidden');
            deliveryFields.classList.add('hidden');
            deliveryZoneSelect.removeAttribute('required');
            deliveryAddressTextarea.removeAttribute('required');
        }

        updateDeliveryFee();
    }

    function updateDeliveryFee() {
        const method = document.querySelector('input[name="fulfillment_method"]:checked')?.value || 'pickup';
        const subtotal = parseFloat(document.getElementById('cartSubtotalText').dataset.subtotal) || 0;
        const feeDisplay = document.getElementById('deliveryFeeDisplay');
        const totalDisplay = document.getElementById('grandTotalDisplay');

        let fee = 0;
        if (method === 'delivery') {
            const select = document.getElementById('delivery_zone_id');
            const selectedOption = select.options[select.selectedIndex];
            fee = parseFloat(selectedOption?.dataset?.fee || 0);
        }

        feeDisplay.textContent = 'Rp ' + fee.toLocaleString('id-ID');
        const grandTotal = subtotal + fee;
        totalDisplay.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }

    function togglePaymentMethodInfo() {
        const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'midtrans';
        const midtransNotice = document.getElementById('paymentMidtransNotice');
        const codNotice = document.getElementById('paymentCodNotice');

        if (method === 'cod') {
            midtransNotice?.classList.add('hidden');
            codNotice?.classList.remove('hidden');
        } else {
            midtransNotice?.classList.remove('hidden');
            codNotice?.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        toggleFulfillmentFields();
        togglePaymentMethodInfo();
    });
</script>
@endsection
