@extends('layouts.admin')

@section('title', 'Penyesuaian Stok')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.stock.index') }}" class="text-sm font-medium text-red-600 hover:text-red-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Persediaan Stok
        </a>
    </div>

    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Catat Penyesuaian & Stok Masuk</h1>
        <p class="text-sm text-slate-500 mt-1">Gunakan formulir ini untuk mencatat penerimaan daging segar, koreksi audit fisik, atau daging rusak/afkir.</p>
    </div>

    <!-- Adjustment Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <form method="POST" action="{{ route('admin.stock.adjust.store') }}" class="p-6 space-y-6" id="stockAdjustmentForm">
            @csrf

            <!-- Product Selection -->
            <div>
                <label for="product_id" class="block text-sm font-semibold text-slate-800 mb-1">
                    Pilih Produk Daging <span class="text-red-600">*</span>
                </label>
                <select name="product_id" 
                        id="product_id" 
                        required
                        class="w-full px-3.5 py-2.5 text-sm rounded-lg border @error('product_id') border-red-500 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" 
                                data-stock="{{ $product->stock_gram }}"
                                data-reserved="{{ $product->reserved_stock_gram }}"
                                data-available="{{ $product->available_stock_gram }}"
                                {{ old('product_id', $selectedProductId) == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} (SKU: {{ $product->sku }}) — Stok Tersedia: {{ number_format($product->available_stock_gram) }} g
                        </option>
                    @endforeach
                </select>
                @error('product_id')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror

                <!-- Live Product Stock Badge -->
                <div id="productStockInfo" class="mt-2 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-600 grid grid-cols-3 gap-2 text-center hidden">
                    <div>
                        <span class="block text-slate-400">Stok Fisik Saat Ini</span>
                        <strong class="text-slate-800 text-sm font-mono" id="currentPhysical">0 g</strong>
                    </div>
                    <div>
                        <span class="block text-amber-600">Dicadangkan (Online)</span>
                        <strong class="text-amber-700 text-sm font-mono" id="currentReserved">0 g</strong>
                    </div>
                    <div>
                        <span class="block text-emerald-600">Stok Tersedia (Jual)</span>
                        <strong class="text-emerald-700 text-sm font-mono" id="currentAvailable">0 g</strong>
                    </div>
                </div>
            </div>

            <!-- Movement Type Selection -->
            <div>
                <label class="block text-sm font-semibold text-slate-800 mb-1">
                    Jenis Mutasi / Penyesuaian <span class="text-red-600">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                    <label class="relative flex items-start p-3.5 rounded-lg border border-slate-200 hover:border-slate-300 bg-white cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/30 transition">
                        <input type="radio" name="movement_type" value="inbound" class="mt-0.5 text-emerald-600 focus:ring-emerald-500" {{ old('movement_type', 'inbound') === 'inbound' ? 'checked' : '' }}>
                        <div class="ml-3 text-xs">
                            <span class="font-semibold text-slate-900 block">Stok Masuk (Inbound)</span>
                            <span class="text-slate-500">Penerimaan pasokan daging segar baru dari supplier / RPH (+)</span>
                        </div>
                    </label>

                    <label class="relative flex items-start p-3.5 rounded-lg border border-slate-200 hover:border-slate-300 bg-white cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/30 transition">
                        <input type="radio" name="movement_type" value="adjustment_in" class="mt-0.5 text-emerald-600 focus:ring-emerald-500" {{ old('movement_type') === 'adjustment_in' ? 'checked' : '' }}>
                        <div class="ml-3 text-xs">
                            <span class="font-semibold text-slate-900 block">Koreksi Tambah (Audit +)</span>
                            <span class="text-slate-500">Hasil audit opname fisik ternyata lebih banyak dari sistem (+)</span>
                        </div>
                    </label>

                    <label class="relative flex items-start p-3.5 rounded-lg border border-slate-200 hover:border-slate-300 bg-white cursor-pointer has-[:checked]:border-red-500 has-[:checked]:bg-red-50/30 transition">
                        <input type="radio" name="movement_type" value="adjustment_out" class="mt-0.5 text-red-600 focus:ring-red-500" {{ old('movement_type') === 'adjustment_out' ? 'checked' : '' }}>
                        <div class="ml-3 text-xs">
                            <span class="font-semibold text-slate-900 block">Koreksi Kurang (Audit -)</span>
                            <span class="text-slate-500">Hasil audit opname fisik ternyata lebih sedikit dari sistem (-)</span>
                        </div>
                    </label>

                    <label class="relative flex items-start p-3.5 rounded-lg border border-slate-200 hover:border-slate-300 bg-white cursor-pointer has-[:checked]:border-red-500 has-[:checked]:bg-red-50/30 transition">
                        <input type="radio" name="movement_type" value="damaged" class="mt-0.5 text-red-600 focus:ring-red-500" {{ old('movement_type') === 'damaged' ? 'checked' : '' }}>
                        <div class="ml-3 text-xs">
                            <span class="font-semibold text-slate-900 block">Daging Rusak / Afkir / Susut</span>
                            <span class="text-slate-500">Penyusutan pemotongan, pembusukan, atau daging rusak (-)</span>
                        </div>
                    </label>
                </div>
                @error('movement_type')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Weight Input -->
            <div>
                <label for="weight_gram" class="block text-sm font-semibold text-slate-800 mb-1">
                    Berat Penyesuaian (Gram) <span class="text-red-600">*</span>
                </label>
                <div class="relative">
                    <input type="number" 
                           name="weight_gram" 
                           id="weight_gram" 
                           value="{{ old('weight_gram') }}" 
                           min="1" 
                           step="1"
                           placeholder="Contoh: 5000 untuk 5 kg"
                           required
                           class="w-full px-3.5 py-2.5 text-sm rounded-lg border @error('weight_gram') border-red-500 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 pr-16 font-mono">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                        gram
                    </div>
                </div>
                <div class="mt-1 flex items-center justify-between text-xs text-slate-500">
                    <span id="weightEquivalentKg">Setara: 0.00 kg</span>
                    <span class="text-slate-400">1.000 gram = 1 kg</span>
                </div>
                @error('weight_gram')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Reason / Notes -->
            <div>
                <label for="reason" class="block text-sm font-semibold text-slate-800 mb-1">
                    Alasan / Keterangan Penyesuaian <span class="text-red-600">*</span>
                </label>
                <input type="text" 
                       name="reason" 
                       id="reason" 
                       value="{{ old('reason') }}" 
                       placeholder="Contoh: Penerimaan daging segar supplier PT Berkah Daging / Susut trimming 150g"
                       required
                       maxlength="255"
                       class="w-full px-3.5 py-2.5 text-sm rounded-lg border @error('reason') border-red-500 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                <p class="text-xs text-slate-400 mt-1">Alasan wajib dicatat untuk transparansi audit buku besar mutasi.</p>
                @error('reason')
                    <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notice & Action Buttons -->
            <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    Stok dicatat otomatis atas nama pengguna: <strong class="text-slate-700">{{ auth()->user()->name }}</strong>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('admin.stock.index') }}" 
                       class="flex-1 sm:flex-none text-center px-4 py-2 text-sm font-medium rounded-lg text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition">
                        Batal
                    </a>
                    <button type="submit" 
                            id="submitAdjustmentBtn"
                            class="flex-1 sm:flex-none px-5 py-2 text-sm font-medium rounded-lg text-white bg-red-700 hover:bg-red-800 transition shadow-sm">
                        Simpan Penyesuaian
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const productSelect = document.getElementById('product_id');
        const stockInfoBox = document.getElementById('productStockInfo');
        const currentPhysical = document.getElementById('currentPhysical');
        const currentReserved = document.getElementById('currentReserved');
        const currentAvailable = document.getElementById('currentAvailable');
        const weightInput = document.getElementById('weight_gram');
        const weightKgSpan = document.getElementById('weightEquivalentKg');

        function updateStockInfo() {
            const selected = productSelect.options[productSelect.selectedIndex];
            if (selected && selected.value) {
                const stock = parseInt(selected.dataset.stock || 0);
                const reserved = parseInt(selected.dataset.reserved || 0);
                const available = parseInt(selected.dataset.available || 0);

                currentPhysical.textContent = stock.toLocaleString('id-ID') + ' g';
                currentReserved.textContent = reserved.toLocaleString('id-ID') + ' g';
                currentAvailable.textContent = available.toLocaleString('id-ID') + ' g';
                stockInfoBox.classList.remove('hidden');
            } else {
                stockInfoBox.classList.add('hidden');
            }
        }

        function updateKgDisplay() {
            const val = parseFloat(weightInput.value) || 0;
            const kg = (val / 1000).toFixed(2);
            weightKgSpan.textContent = `Setara: ${kg} kg`;
        }

        productSelect.addEventListener('change', updateStockInfo);
        weightInput.addEventListener('input', updateKgDisplay);

        updateStockInfo();
        updateKgDisplay();
    });
</script>
@endsection
