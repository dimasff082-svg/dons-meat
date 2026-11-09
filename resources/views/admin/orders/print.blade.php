<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label Pengiriman & Struk {{ $order->order_code }}</title>

    <!-- Tailwind CSS (compiled) -->
    @vite(['resources/css/app.css'])

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-container {
                box-shadow: none !important;
                border: 2px solid black !important;
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 12px !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex flex-col items-center justify-start text-slate-900 font-sans antialiased">

    <!-- Top Action Bar (Hidden on print) -->
    <div class="no-print max-w-lg w-full mb-4 flex items-center justify-between">
        <a href="{{ route('admin.orders.show', $order) }}" 
           class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-900 gap-1 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-xs">
            &larr; Kembali ke Detail Pesanan
        </a>
        <div class="flex items-center gap-2">
            <button type="button" 
                    onclick="window.print()" 
                    class="inline-flex items-center px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold shadow-md transition gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Label & Struk</span>
            </button>
        </div>
    </div>

    <!-- Main Print Label Card (Ready to paste on package) -->
    <div class="print-container bg-white border-2 border-slate-900 rounded-2xl shadow-xl max-w-lg w-full p-6 text-slate-900 space-y-4">
        
        <!-- Header Toko -->
        <div class="border-b-2 border-slate-900 pb-3 text-center space-y-1">
            <div class="flex items-center justify-center gap-2">
                @if($storeSetting->logo_path && file_exists(public_path('storage/' . $storeSetting->logo_path)))
                    <img src="{{ asset('storage/' . $storeSetting->logo_path) }}" alt="{{ $storeSetting->business_name }}" class="h-10 w-auto object-contain">
                @else
                    <div class="w-9 h-9 rounded-xl bg-red-800 text-white flex items-center justify-center font-black text-xl">
                        D
                    </div>
                @endif
                <div class="text-left">
                    <h1 class="text-xl font-black tracking-wider leading-none">{{ $storeSetting->business_name ?? 'DONS MEAT' }}</h1>
                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest block">Pusat Daging Berkualitas & Segar</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-600 mt-1">
                {{ $storeSetting->address ?? 'Makassar, Sulawesi Selatan' }} &bull; Telp/WA: {{ $storeSetting->phone ?? '-' }}
            </p>
            <div class="pt-1">
                <span class="inline-block px-3 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-slate-900 text-white tracking-widest">
                    LABEL PENGIRIMAN & NOTA KEMASAN
                </span>
            </div>
        </div>

        <!-- Order Meta Info -->
        <div class="grid grid-cols-2 gap-2 text-xs border-b border-dashed border-slate-400 pb-3">
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-bold">No. Pesanan / Resi:</span>
                <span class="font-mono font-black text-base tracking-tight text-slate-900">{{ $order->order_code }}</span>
            </div>
            <div class="text-right">
                <span class="text-slate-500 block text-[10px] uppercase font-bold">Waktu Pesan:</span>
                <span class="font-semibold text-slate-800">{{ $order->created_at->translatedFormat('d/m/Y, H:i') }} WITA</span>
            </div>
        </div>

        <!-- KOTAK TUJUAN PENGANTARAN (KHUSUS ACUAN KURIR) -->
        <div class="border-2 border-slate-900 rounded-xl overflow-hidden bg-slate-50">
            <div class="bg-slate-900 text-white px-3 py-1 flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                <span>📍 TUJUAN PENGANTARAN (UNTUK KURIR)</span>
                <span class="text-amber-300 font-extrabold">{{ $order->fulfillment_method?->label() }}</span>
            </div>
            
            <div class="p-3.5 space-y-2 text-xs">
                <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-1">
                    <div>
                        <span class="text-slate-500 text-[10px] uppercase font-semibold">Nama Penerima:</span>
                        <div class="text-base font-extrabold text-slate-900">{{ $order->customer_name }}</div>
                    </div>
                    <div>
                        <span class="text-slate-500 text-[10px] uppercase font-semibold">No. HP / WhatsApp:</span>
                        <div class="text-sm font-extrabold font-mono text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-300 inline-block">
                            📞 {{ $order->customer_phone }}
                        </div>
                    </div>
                </div>

                @if($order->fulfillment_method === \App\Enums\FulfillmentMethod::Delivery)
                    <div class="pt-1 border-t border-slate-200">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold">Alamat Lengkap Pengantaran:</span>
                        <p class="text-xs font-bold text-slate-900 leading-relaxed mt-0.5 whitespace-pre-line bg-white p-2 rounded-lg border border-slate-200">
                            {{ $order->delivery_address ?: '-' }}
                        </p>
                    </div>

                    @if($order->deliveryZone)
                        <div class="flex items-center gap-1.5 text-xs">
                            <span class="text-slate-500 font-semibold">Wilayah/Zona:</span>
                            <span class="px-2 py-0.5 bg-slate-200 text-slate-800 font-bold rounded">
                                {{ $order->deliveryZone->name }}
                            </span>
                        </div>
                    @endif
                @else
                    <div class="pt-1 border-t border-slate-200 text-slate-700">
                        <span class="text-slate-500 text-[10px] uppercase font-semibold">Metode Pengambilan:</span>
                        <p class="font-bold text-slate-900">AMBIL SENDIRI DI GERAI DONS MEAT</p>
                    </div>
                @endif

                @if($order->customer_note)
                    <div class="pt-1.5 border-t border-dashed border-slate-300 bg-amber-50/80 p-2 rounded-lg border border-amber-200 text-amber-950">
                        <span class="font-extrabold text-[11px] block uppercase text-amber-900">📌 Patokan / Catatan Khusus Kurir:</span>
                        <p class="text-xs italic font-medium mt-0.5">{{ $order->customer_note }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- KOTAK STATUS PEMBAYARAN & INSTRUKSI KURIR -->
        @php
            $isPaid = $order->isPaid();
            $paymentMethod = $order->payment?->payment_type ?? 'midtrans';
            $isCod = strtolower($paymentMethod) === 'cod' || str_contains(strtolower($order->customer_note ?? ''), 'cod');
        @endphp

        <div class="border-2 {{ $isPaid ? 'border-emerald-700 bg-emerald-50' : 'border-red-700 bg-red-50' }} rounded-xl p-3 text-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider block {{ $isPaid ? 'text-emerald-800' : 'text-red-800' }}">
                        Instruksi Penagihan Kurir:
                    </span>
                    @if($isPaid)
                        <div class="text-sm font-black text-emerald-900 flex items-center gap-1">
                            <span>✅ SUDAH LUNAS (PAID)</span>
                        </div>
                        <span class="text-[11px] text-emerald-800 font-medium block mt-0.5">
                            Kurir <strong>TIDAK PERLU</strong> menagih uang apa pun ke pembeli.
                        </span>
                    @else
                        <div class="text-sm font-black text-red-900 flex items-center gap-1">
                            <span>⚠️ TAGIH PEMBAYARAN (COD / BELUM LUNAS)</span>
                        </div>
                        <span class="text-[11px] text-red-800 font-semibold block mt-0.5">
                            Kurir <strong>WAJIB MENAGIH</strong> uang tunai sebesar:
                        </span>
                    @endif
                </div>

                <div class="text-right">
                    <span class="text-[10px] text-slate-500 uppercase font-semibold block">Total Tagihan:</span>
                    <div class="text-lg font-black font-mono {{ $isPaid ? 'text-emerald-700' : 'text-red-700' }}">
                        Rp {{ number_format($isPaid ? 0 : $order->total_amount, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- RINCIAN ITEM KEMASAN DAGING -->
        <div class="space-y-1 text-xs">
            <span class="text-[10px] font-bold uppercase text-slate-500 tracking-wider block">
                Isi Kemasan Paket Daging:
            </span>
            <table class="w-full text-left border border-slate-300 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 text-[11px] text-slate-700 uppercase">
                    <tr>
                        <th class="p-1.5 pl-2">Produk & Varian</th>
                        <th class="p-1.5 text-center">Qty</th>
                        <th class="p-1.5 text-right pr-2">Berat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @php $totalGram = 0; @endphp
                    @foreach($order->items as $item)
                        @php $totalGram += $item->total_weight_gram; @endphp
                        <tr>
                            <td class="p-1.5 pl-2 font-bold text-slate-900 text-xs">
                                {{ $item->product_name_snapshot }}
                                <span class="block text-[10px] font-normal text-slate-500">
                                    Kemasan {{ number_format($item->weight_per_package_gram) }} g
                                </span>
                            </td>
                            <td class="p-1.5 text-center font-bold font-mono">{{ $item->quantity }}x</td>
                            <td class="p-1.5 text-right pr-2 font-mono font-semibold text-slate-800">
                                {{ number_format($item->total_weight_gram) }} g
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-300 text-xs font-bold">
                    <tr>
                        <td colspan="2" class="p-1.5 pl-2 text-slate-700 uppercase text-[11px]">Total Berat Paket:</td>
                        <td class="p-1.5 text-right pr-2 font-mono text-slate-900">
                            {{ number_format($totalGram) }} g ({{ number_format($totalGram / 1000, 2) }} kg)
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer / Safety Guidance -->
        <div class="border-t border-dashed border-slate-400 pt-2 text-center text-[10px] text-slate-500 space-y-0.5">
            <p class="font-bold text-slate-700">❄️ DAGING SEGAR DONS MEAT — SEGERA SIMPAN DI FREEZER / CHILLER</p>
            <p>Terima kasih atas pesanan Anda! Informasi & Layanan: {{ $storeSetting->phone ?? '-' }}</p>
        </div>

    </div>

    <script>
        // Trigger auto-print dialog on page load
        window.addEventListener('DOMContentLoaded', () => {
            // Optional brief delay to let CSS render
            setTimeout(() => {
                window.print();
            }, 400);
        });
    </script>
</body>
</html>

