@extends('layouts.admin')

@section('title', 'Penjualan Langsung (Kasir)')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Kasir Penjualan Langsung</h1>
            <p class="text-sm text-slate-500 mt-1">Transaksi langsung di toko fisik oleh kasir/admin tanpa role terpisah. Stok berkurang seketika.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.direct-sales.index') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Riwayat Transaksi Toko
            </a>
        </div>
    </div>

    <!-- POS Layout: 2 Columns on desktop -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Product Selection (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            <!-- Search bar -->
            <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       id="productSearchInput" 
                       placeholder="Cari daging (nama produk atau SKU)..."
                       class="w-full text-sm bg-transparent focus:outline-none text-slate-800 placeholder-slate-400">
                <button type="button" id="clearSearchBtn" class="text-slate-400 hover:text-slate-600 hidden">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="productsGrid">
                @forelse($products as $product)
                    @php
                        $availableStock = $product->available_stock_gram;
                    @endphp
                    <div class="product-card bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:border-red-400 hover:shadow transition flex flex-col justify-between"
                         data-name="{{ strtolower($product->name) }}"
                         data-sku="{{ strtolower($product->sku) }}">
                        <div>
                            <div class="flex items-start gap-3">
                                <div class="w-14 h-14 rounded-lg bg-slate-100 border border-slate-200 shrink-0 overflow-hidden flex items-center justify-center">
                                    @if($product->image_path)
                                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs font-bold text-slate-400">{{ substr($product->name, 0, 2) }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-red-600">{{ $product->category->name ?? 'Daging' }}</div>
                                    <h3 class="font-bold text-slate-900 text-sm truncate">{{ $product->name }}</h3>
                                    <div class="text-xs font-mono text-slate-500">{{ $product->sku }}</div>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-500">Harga:</span>
                                    <span class="font-bold text-slate-900 font-mono">Rp {{ number_format($product->price_per_kg, 0, ',', '.') }}/kg</span>
                                </div>
                                <div>
                                    <span class="text-slate-500">Stok:</span>
                                    <span class="font-semibold text-emerald-700 font-mono">{{ number_format($availableStock) }} g</span>
                                </div>
                            </div>
                        </div>

                        <!-- Weight options buttons -->
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <div class="text-[11px] font-medium text-slate-500 mb-1.5">Pilih Kemasan:</div>
                            <div class="grid grid-cols-3 gap-1.5">
                                @forelse($product->activeWeightOptions as $opt)
                                    @php
                                        $optPrice = round(($product->price_per_kg * $opt->weight_gram) / 1000);
                                        $canAfford = $availableStock >= $opt->weight_gram;
                                    @endphp
                                    <button type="button"
                                            class="add-to-cart-btn px-2 py-1.5 rounded-lg border text-xs font-medium text-center transition flex flex-col items-center justify-center {{ $canAfford ? 'border-slate-200 hover:border-red-600 hover:bg-red-50 text-slate-800' : 'border-slate-100 bg-slate-50 text-slate-300 cursor-not-allowed' }}"
                                            {{ $canAfford ? '' : 'disabled' }}
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            data-product-sku="{{ $product->sku }}"
                                            data-option-id="{{ $opt->id }}"
                                            data-weight-gram="{{ $opt->weight_gram }}"
                                            data-weight-label="{{ $opt->label }}"
                                            data-price-per-kg="{{ $product->price_per_kg }}"
                                            data-item-price="{{ $optPrice }}"
                                            data-stock-available="{{ $availableStock }}">
                                        <span class="font-semibold">{{ $opt->label }}</span>
                                        <span class="text-[10px] text-slate-500 font-mono">Rp {{ number_format($optPrice, 0, ',', '.') }}</span>
                                    </button>
                                @empty
                                    <div class="col-span-3 text-center text-xs text-slate-400 py-1">
                                        Tidak ada opsi berat aktif
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 py-12 text-center text-slate-400 bg-white rounded-xl border border-slate-200">
                        Tidak ada produk aktif yang memiliki stok siap jual saat ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Current Bill / Checkout Form (5 cols) -->
        <div class="lg:col-span-5">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden sticky top-4">
                <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-base">Nota Pesanan Langsung</h2>
                        <p class="text-xs text-slate-400">Kasir: {{ auth()->user()->name }} ({{ auth()->user()->role->label() }})</p>
                    </div>
                    <button type="button" id="clearCartBtn" class="text-xs text-red-300 hover:text-white px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 transition">
                        Kosongkan
                    </button>
                </div>

                @if($errors->any())
                    <div class="p-4 bg-red-50 border-b border-red-200 text-red-700 text-xs">
                        <strong class="font-semibold">Perhatian:</strong>
                        <ul class="list-disc list-inside mt-1 space-y-0.5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.direct-sales.store') }}" id="directSaleForm">
                    @csrf

                    <!-- Customer Info (Optional) -->
                    <div class="p-4 border-b border-slate-200 bg-slate-50/50 space-y-2.5">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-0.5">Nama Pelanggan</label>
                                <input type="text" 
                                       name="customer_name" 
                                       id="customerNameInput" 
                                       value="{{ old('customer_name', 'Pelanggan Toko') }}"
                                       placeholder="Walk-in / Nama..."
                                       class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:outline-none focus:ring-1 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-0.5">No. HP / WA (Opsional)</label>
                                <input type="text" 
                                       name="customer_phone" 
                                       id="customerPhoneInput" 
                                       value="{{ old('customer_phone') }}"
                                       placeholder="08..."
                                       class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:outline-none focus:ring-1 focus:ring-red-500">
                            </div>
                        </div>
                    </div>

                    <!-- Cart Items List -->
                    <div class="p-4 max-h-72 overflow-y-auto divide-y divide-slate-100" id="cartItemsContainer">
                        <div id="cartEmptyNotice" class="py-10 text-center text-slate-400 text-xs">
                            <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            Belum ada produk yang dipilih.<br>Klik pilihan berat pada produk di sebelah kiri untuk menambah ke nota.
                        </div>
                    </div>

                    <!-- Summary & Payment Details -->
                    <div class="p-4 bg-slate-50 border-t border-slate-200 space-y-3">
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between text-slate-600">
                                <span>Total Item</span>
                                <span class="font-mono font-medium" id="totalItemsCount">0 paket</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Total Berat Daging</span>
                                <span class="font-mono font-medium" id="totalWeightDisplay">0 g (0.00 kg)</span>
                            </div>
                            <div class="flex justify-between text-slate-900 font-bold text-base pt-2 border-t border-slate-200">
                                <span>Total Pembayaran</span>
                                <span class="text-red-700 font-mono" id="totalAmountDisplay">Rp 0</span>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="pt-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pembayaran</label>
                            <div class="grid grid-cols-3 gap-1.5">
                                <label class="flex items-center justify-center p-2 rounded-lg border border-slate-200 bg-white text-xs font-medium cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50/50 has-[:checked]:text-red-700 transition">
                                    <input type="radio" name="payment_method" value="cash" class="sr-only" checked>
                                    <span>Tunai (Cash)</span>
                                </label>
                                <label class="flex items-center justify-center p-2 rounded-lg border border-slate-200 bg-white text-xs font-medium cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50/50 has-[:checked]:text-red-700 transition">
                                    <input type="radio" name="payment_method" value="qris" class="sr-only">
                                    <span>QRIS Toko</span>
                                </label>
                                <label class="flex items-center justify-center p-2 rounded-lg border border-slate-200 bg-white text-xs font-medium cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50/50 has-[:checked]:text-red-700 transition">
                                    <input type="radio" name="payment_method" value="manual_transfer" class="sr-only">
                                    <span>Transfer</span>
                                </label>
                            </div>
                        </div>

                        <!-- Cash Payment Inputs -->
                        <div id="cashFieldsSection" class="space-y-2 pt-1">
                            <label for="amountPaidDisplay" class="block text-xs font-semibold text-slate-700">Uang Diterima (Rp) <span class="text-red-600">*</span></label>
                            
                            <!-- Hidden input holding raw numeric value for form submission -->
                            <input type="hidden" 
                                   name="amount_paid" 
                                   id="amountPaidInput" 
                                   value="{{ old('amount_paid', 0) }}">

                            <!-- Formatted Display Input with automatic dots separator -->
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">Rp</span>
                                <input type="text" 
                                       id="amountPaidDisplay" 
                                       inputmode="numeric"
                                       autocomplete="off"
                                       value="{{ old('amount_paid') ? number_format((float) old('amount_paid'), 0, ',', '.') : '' }}"
                                       placeholder="0"
                                       class="w-full pl-9 pr-3 py-2 text-sm font-mono font-bold rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500">
                            </div>
                            
                            <!-- Quick amount buttons -->
                            <div class="grid grid-cols-4 gap-1">
                                <button type="button" class="quick-cash-btn px-1.5 py-1 text-[11px] font-medium bg-white border border-slate-200 hover:bg-slate-100 rounded text-slate-700" data-type="exact">
                                    Uang Pas
                                </button>
                                <button type="button" class="quick-cash-btn px-1.5 py-1 text-[11px] font-medium bg-white border border-slate-200 hover:bg-slate-100 rounded text-slate-700" data-value="50000">
                                    50.000
                                </button>
                                <button type="button" class="quick-cash-btn px-1.5 py-1 text-[11px] font-medium bg-white border border-slate-200 hover:bg-slate-100 rounded text-slate-700" data-value="100000">
                                    100.000
                                </button>
                                <button type="button" class="quick-cash-btn px-1.5 py-1 text-[11px] font-medium bg-white border border-slate-200 hover:bg-slate-100 rounded text-slate-700" data-value="200000">
                                    200.000
                                </button>
                            </div>

                            <!-- Change calculation -->
                            <div class="p-2.5 bg-slate-100 rounded-lg flex items-center justify-between text-xs">
                                <span class="text-slate-600 font-medium">Kembalian:</span>
                                <span class="text-sm font-bold font-mono text-emerald-700" id="changeAmountDisplay">Rp 0</span>
                            </div>
                        </div>

                        <!-- Customer Note -->
                        <div>
                            <input type="text" 
                                   name="customer_note" 
                                   placeholder="Catatan tambahan (opsional)..."
                                   class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:outline-none focus:ring-1 focus:ring-red-500">
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" 
                                    id="submitOrderBtn"
                                    disabled
                                    class="w-full py-3 px-4 rounded-xl font-bold text-sm text-white bg-red-700 hover:bg-red-800 disabled:opacity-50 disabled:cursor-not-allowed transition shadow flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Bayar & Selesaikan Transaksi</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let cart = []; // Array of { productId, productName, sku, optionId, weightGram, weightLabel, pricePerKg, itemPrice, quantity, maxAvailableStock }

        const cartContainer = document.getElementById('cartItemsContainer');
        const cartEmptyNotice = document.getElementById('cartEmptyNotice');
        const totalItemsCount = document.getElementById('totalItemsCount');
        const totalWeightDisplay = document.getElementById('totalWeightDisplay');
        const totalAmountDisplay = document.getElementById('totalAmountDisplay');
        const amountPaidInput = document.getElementById('amountPaidInput');
        const amountPaidDisplay = document.getElementById('amountPaidDisplay');
        const changeAmountDisplay = document.getElementById('changeAmountDisplay');
        const submitOrderBtn = document.getElementById('submitOrderBtn');
        const searchInput = document.getElementById('productSearchInput');
        const clearSearchBtn = document.getElementById('clearSearchBtn');
        const productCards = document.querySelectorAll('.product-card');

        // Search product filter
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase().trim();
            clearSearchBtn.classList.toggle('hidden', query === '');

            productCards.forEach(card => {
                const name = card.dataset.name;
                const sku = card.dataset.sku;
                if (name.includes(query) || sku.includes(query)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });

        clearSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            clearSearchBtn.classList.add('hidden');
            productCards.forEach(card => card.classList.remove('hidden'));
        });

        // Add to cart click
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const productId = parseInt(btn.dataset.productId);
                const productName = btn.dataset.productName;
                const sku = btn.dataset.productSku;
                const optionId = parseInt(btn.dataset.optionId);
                const weightGram = parseInt(btn.dataset.weightGram);
                const weightLabel = btn.dataset.weightLabel;
                const pricePerKg = parseFloat(btn.dataset.pricePerKg);
                const itemPrice = parseFloat(btn.dataset.itemPrice);
                const maxAvailable = parseInt(btn.dataset.stockAvailable);

                // Check existing in cart
                const existingIndex = cart.findIndex(i => i.optionId === optionId);
                if (existingIndex > -1) {
                    // Check if adding one more exceeds available stock
                    const currentTotalWeightForProduct = cart
                        .filter(i => i.productId === productId)
                        .reduce((sum, i) => sum + (i.weightGram * i.quantity), 0);
                    
                    if (currentTotalWeightForProduct + weightGram > maxAvailable) {
                        alert(`Stok tidak mencukupi untuk menambah ${productName}. Tersedia: ${maxAvailable.toLocaleString('id-ID')} g.`);
                        return;
                    }
                    cart[existingIndex].quantity += 1;
                } else {
                    cart.push({
                        productId,
                        productName,
                        sku,
                        optionId,
                        weightGram,
                        weightLabel,
                        pricePerKg,
                        itemPrice,
                        quantity: 1,
                        maxAvailable
                    });
                }

                renderCart();
            });
        });

        function renderCart() {
            if (cart.length === 0) {
                cartContainer.innerHTML = '';
                cartContainer.appendChild(cartEmptyNotice);
                totalItemsCount.textContent = '0 paket';
                totalWeightDisplay.textContent = '0 g (0.00 kg)';
                totalAmountDisplay.textContent = 'Rp 0';
                setAmountPaid(0);
                changeAmountDisplay.textContent = 'Rp 0';
                submitOrderBtn.disabled = true;
                return;
            }

            let html = '';
            let totalQty = 0;
            let totalWeight = 0;
            let subtotal = 0;

            cart.forEach((item, index) => {
                const lineWeight = item.weightGram * item.quantity;
                const lineSubtotal = item.itemPrice * item.quantity;

                totalQty += item.quantity;
                totalWeight += lineWeight;
                subtotal += lineSubtotal;

                html += `
                    <div class="py-2.5 flex items-center justify-between gap-2">
                        <!-- Hidden inputs for Laravel backend -->
                        <input type="hidden" name="items[${index}][product_id]" value="${item.productId}">
                        <input type="hidden" name="items[${index}][weight_option_id]" value="${item.optionId}">
                        <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">

                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-slate-800 text-xs truncate">${item.productName}</div>
                            <div class="text-[11px] text-slate-500">${item.weightLabel} @ Rp ${item.itemPrice.toLocaleString('id-ID')}</div>
                            <div class="text-[11px] text-red-700 font-mono font-semibold">Rp ${lineSubtotal.toLocaleString('id-ID')}</div>
                        </div>

                        <!-- Quantity Stepper -->
                        <div class="flex items-center gap-1">
                            <button type="button" class="btn-qty-minus w-6 h-6 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold" data-index="${index}">-</button>
                            <span class="w-6 text-center text-xs font-mono font-bold">${item.quantity}</span>
                            <button type="button" class="btn-qty-plus w-6 h-6 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold" data-index="${index}">+</button>
                            <button type="button" class="btn-item-remove ml-1 text-slate-400 hover:text-red-600 p-1" data-index="${index}" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
            });

            cartContainer.innerHTML = html;

            // Bind stepper events
            cartContainer.querySelectorAll('.btn-qty-minus').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.dataset.index);
                    if (cart[idx].quantity > 1) {
                        cart[idx].quantity -= 1;
                    } else {
                        cart.splice(idx, 1);
                    }
                    renderCart();
                });
            });

            cartContainer.querySelectorAll('.btn-qty-plus').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.dataset.index);
                    const item = cart[idx];
                    const currentTotalWeightForProduct = cart
                        .filter(i => i.productId === item.productId)
                        .reduce((sum, i) => sum + (i.weightGram * i.quantity), 0);

                    if (currentTotalWeightForProduct + item.weightGram > item.maxAvailable) {
                        alert(`Stok tidak mencukupi untuk menambah ${item.productName}.`);
                        return;
                    }
                    item.quantity += 1;
                    renderCart();
                });
            });

            cartContainer.querySelectorAll('.btn-item-remove').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.dataset.index);
                    cart.splice(idx, 1);
                    renderCart();
                });
            });

            totalItemsCount.textContent = `${totalQty} paket`;
            totalWeightDisplay.textContent = `${totalWeight.toLocaleString('id-ID')} g (${(totalWeight / 1000).toFixed(2)} kg)`;
            totalAmountDisplay.textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;

            // Do NOT auto-fill amount paid when products are added.
            // Leave it empty so cashier enters it manually or clicks a quick button.
            updatePaymentCalculations();
        }

        // Helper functions for Indonesian Rupiah dots formatting
        function parseRupiahNumber(str) {
            if (typeof str === 'number') return Math.max(0, Math.round(str));
            const cleanStr = String(str || '').replace(/[^0-9]/g, '');
            return cleanStr === '' ? 0 : parseInt(cleanStr, 10);
        }

        function setAmountPaid(val) {
            const num = parseRupiahNumber(val);
            amountPaidInput.value = num;
            amountPaidDisplay.value = num > 0 ? num.toLocaleString('id-ID') : '';
            updatePaymentCalculations();
        }

        // Real-time automatic formatting as user types (e.g. 3000 -> 3.000, 43750 -> 43.750)
        amountPaidDisplay.addEventListener('input', () => {
            const cursorPosition = amountPaidDisplay.selectionStart;
            const originalLength = amountPaidDisplay.value.length;

            const rawVal = parseRupiahNumber(amountPaidDisplay.value);

            if (rawVal === 0 && amountPaidDisplay.value.trim() === '') {
                amountPaidDisplay.value = '';
                amountPaidInput.value = 0;
            } else {
                const formatted = rawVal.toLocaleString('id-ID');
                amountPaidDisplay.value = formatted;
                amountPaidInput.value = rawVal;

                // Keep cursor position stable when dots are inserted
                const newLength = formatted.length;
                const newCursor = Math.max(0, cursorPosition + (newLength - originalLength));
                try {
                    amountPaidDisplay.setSelectionRange(newCursor, newCursor);
                } catch (e) {}
            }

            updatePaymentCalculations();
        });

        function updatePaymentCalculations() {
            const subtotal = cart.reduce((sum, i) => sum + (i.itemPrice * i.quantity), 0);
            const paid = parseFloat(amountPaidInput.value) || 0;
            const change = Math.max(0, paid - subtotal);

            const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';

            if (selectedPaymentMethod === 'cash') {
                if (paid === 0 && subtotal > 0) {
                    changeAmountDisplay.textContent = 'Masukkan uang diterima';
                    changeAmountDisplay.classList.remove('text-emerald-700', 'text-red-600');
                    changeAmountDisplay.classList.add('text-amber-600');
                    submitOrderBtn.disabled = true;
                } else if (paid < subtotal && subtotal > 0) {
                    changeAmountDisplay.textContent = 'Kurang Rp ' + (subtotal - paid).toLocaleString('id-ID');
                    changeAmountDisplay.classList.remove('text-emerald-700', 'text-amber-600');
                    changeAmountDisplay.classList.add('text-red-600');
                    submitOrderBtn.disabled = true;
                } else {
                    changeAmountDisplay.textContent = `Rp ${change.toLocaleString('id-ID')}`;
                    changeAmountDisplay.classList.remove('text-red-600', 'text-amber-600');
                    changeAmountDisplay.classList.add('text-emerald-700');
                    submitOrderBtn.disabled = cart.length === 0;
                }
            } else {
                changeAmountDisplay.textContent = 'Rp 0 (Non-Tunai)';
                changeAmountDisplay.classList.remove('text-red-600', 'text-amber-600');
                changeAmountDisplay.classList.add('text-emerald-700');
                submitOrderBtn.disabled = cart.length === 0;
            }
        }

        // Quick cash buttons
        document.querySelectorAll('.quick-cash-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const subtotal = cart.reduce((sum, i) => sum + (i.itemPrice * i.quantity), 0);
                if (btn.dataset.type === 'exact') {
                    setAmountPaid(subtotal);
                } else {
                    const addVal = parseFloat(btn.dataset.value);
                    setAmountPaid(addVal);
                }
            });
        });

        // Payment method toggle
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', () => {
                const subtotal = cart.reduce((sum, i) => sum + (i.itemPrice * i.quantity), 0);
                if (radio.value !== 'cash') {
                    setAmountPaid(subtotal);
                }
                updatePaymentCalculations();
            });
        });

        // Clear cart button
        document.getElementById('clearCartBtn').addEventListener('click', () => {
            if (cart.length > 0 && confirm('Kosongkan semua daftar belanjaan?')) {
                cart = [];
                renderCart();
            }
        });
    });
</script>
@endsection
