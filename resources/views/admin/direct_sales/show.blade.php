@extends('layouts.admin')

@section('title', 'Nota Transaksi ' . $order->order_code)

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <!-- Top Action Navigation (Hidden on print) -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('admin.direct-sales.create') }}" class="text-sm font-medium text-red-600 hover:text-red-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kasir Transaksi Baru
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.direct-sales.index') }}" class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-sm">
                Riwayat Penjualan
            </a>
            <button type="button" 
                    onclick="window.print()" 
                    class="px-4 py-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Nota</span>
            </button>
        </div>
    </div>

    <!-- Receipt Card (Print Target) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 sm:p-8 font-mono text-slate-900 receipt-box" id="printableReceipt">
        <!-- Receipt Header -->
        <div class="text-center pb-4 border-b border-dashed border-slate-300">
            <h2 class="text-xl font-black tracking-wider uppercase">{{ $storeSetting->business_name ?? 'DONS MEAT' }}</h2>
            <p class="text-xs text-slate-600 mt-1">{{ $storeSetting->address ?? 'Jl. Pengayoman, Makassar' }}</p>
            <p class="text-xs text-slate-600">Telp/WA: {{ $storeSetting->phone ?? '-' }}</p>
            <div class="mt-2 inline-block px-3 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase">
                Nota Penjualan Langsung (Toko)
            </div>
        </div>

        <!-- Order Meta -->
        <div class="py-3 border-b border-dashed border-slate-300 text-xs space-y-1">
            <div class="flex justify-between">
                <span class="text-slate-500">No. Nota:</span>
                <span class="font-bold font-mono">{{ $order->order_code }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Tanggal:</span>
                <span>{{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Kasir/Petugas:</span>
                <span>{{ $order->createdByUser->name ?? 'Admin' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Pelanggan:</span>
                <span class="font-medium">{{ $order->customer_name }}</span>
            </div>
            @if($order->customer_phone && $order->customer_phone !== '-')
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Kontak:</span>
                    <span>{{ $order->customer_phone }}</span>
                </div>
            @endif
        </div>

        <!-- Items Table -->
        <div class="py-3 border-b border-dashed border-slate-300 text-xs">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-slate-500 border-b border-slate-200 pb-1">
                        <th class="py-1">Item / Berat</th>
                        <th class="py-1 text-center">Qty</th>
                        <th class="py-1 text-right">Harga</th>
                        <th class="py-1 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $totalWeightAll = 0;
                    @endphp
                    @foreach($order->items as $item)
                        @php
                            $totalWeightAll += $item->total_weight_gram;
                        @endphp
                        <tr>
                            <td class="py-2 pr-2">
                                <div class="font-bold text-slate-900">{{ $item->product_name_snapshot }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ number_format($item->weight_per_package_gram) }}g (Rp {{ number_format($item->price_per_kg_snapshot, 0, ',', '.') }}/kg)
                                </div>
                            </td>
                            <td class="py-2 text-center font-mono">{{ $item->quantity }}</td>
                            <td class="py-2 text-right font-mono text-[11px] text-slate-600">
                                Rp {{ number_format($item->subtotal / $item->quantity, 0, ',', '.') }}
                            </td>
                            <td class="py-2 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Payment Summary -->
        <div class="py-3 border-b border-dashed border-slate-300 text-xs space-y-1.5">
            <div class="flex justify-between text-slate-600">
                <span>Total Berat Daging:</span>
                <span class="font-bold font-mono">{{ number_format($totalWeightAll) }} g ({{ number_format($totalWeightAll / 1000, 2) }} kg)</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Subtotal Belanja:</span>
                <span class="font-mono">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Biaya Layanan/Ongkir:</span>
                <span class="font-mono">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-900 font-black text-sm pt-2 border-t border-slate-200">
                <span>TOTAL:</span>
                <span class="font-mono">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Payment Details -->
        @php
            $payment = $order->payments->first();
            $meta = $payment->metadata ?? [];
            $amountPaid = $meta['amount_paid'] ?? $order->total_amount;
            $change = $meta['change'] ?? 0;
            $methodLabel = match($payment->payment_type ?? 'cash') {
                'cash' => 'Tunai (Cash)',
                'qris' => 'QRIS Toko',
                'manual_transfer' => 'Transfer Bank',
                default => strtoupper($payment->payment_type ?? 'Tunai'),
            };
        @endphp
        <div class="py-3 border-b border-dashed border-slate-300 text-xs space-y-1">
            <div class="flex justify-between">
                <span class="text-slate-500">Metode Bayar:</span>
                <span class="font-semibold">{{ $methodLabel }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Jumlah Diterima:</span>
                <span class="font-mono">Rp {{ number_format($amountPaid, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Kembalian:</span>
                <span class="font-mono font-bold text-slate-900">Rp {{ number_format($change, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center pt-1">
                <span class="text-slate-500">Status Pembayaran:</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                    LUNAS
                </span>
            </div>
        </div>

        @if($order->customer_note)
            <div class="py-2 border-b border-dashed border-slate-300 text-xs">
                <span class="text-slate-500">Catatan:</span>
                <p class="text-slate-800 italic mt-0.5">{{ $order->customer_note }}</p>
            </div>
        @endif

        <!-- Receipt Footer Note -->
        <div class="pt-4 text-center text-xs text-slate-500 space-y-1">
            <p class="font-semibold text-slate-700">Terima kasih atas kunjungan Anda!</p>
            <p class="text-[10px] text-slate-400">Daging segar berkualitas siap olah. Simpan segera di lemari pendingin / freezer.</p>
        </div>
    </div>
</div>

<style>
    @media print {
        body {
            background-color: #fff !important;
        }
        .no-print, aside, header, #sidebarBackdrop, #sidebarLogoutBtn {
            display: none !important;
        }
        main {
            padding: 0 !important;
            margin: 0 !important;
        }
        .receipt-box {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }
    }
</style>
@endsection
