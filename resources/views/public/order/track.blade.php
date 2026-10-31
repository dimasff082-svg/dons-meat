@extends('layouts.public')

@section('title', 'Status Pesanan ' . $order->order_code . ' - DONS MEAT')

@section('content')
<div class="py-8 sm:py-12 bg-slate-50 min-h-[85vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Breadcrumb & Top Alerts -->
        <nav class="flex items-center space-x-2 text-xs text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-red-700">Beranda</a>
            <span>/</span>
            <a href="{{ route('catalog.index') }}" class="hover:text-red-700">Katalog</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Pelacakan Pesanan</span>
        </nav>

        @if(session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Order Header Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Kode Pesanan Online</span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 font-mono tracking-tight">{{ $order->order_code }}</h1>
                    <p class="text-xs text-slate-500 mt-1">Dibuat pada {{ $order->created_at->translatedFormat('d F Y, H:i') }} WITA</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Order Status Badge -->
                    @php
                        $orderStatusBadge = match($order->order_status->value ?? $order->order_status) {
                            'pending_payment' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'processing' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'ready_for_pickup' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'on_delivery' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                            'expired' => 'bg-slate-100 text-slate-600 border-slate-300',
                            default => 'bg-slate-50 text-slate-700 border-slate-200',
                        };

                        $paymentStatusBadge = match($order->payment_status->value ?? $order->payment_status) {
                            'unpaid' => 'bg-amber-100 text-amber-800 border-amber-300',
                            'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                            'failed' => 'bg-red-100 text-red-800 border-red-300',
                            'expired' => 'bg-slate-200 text-slate-700 border-slate-300',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };
                    @endphp

                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $orderStatusBadge }}">
                        {{ $order->order_status->label() }}
                    </span>

                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $paymentStatusBadge }}">
                        {{ $order->payment_status->label() }}
                    </span>
                </div>
            </div>

            <!-- Payment Countdown Alert if Unpaid -->
            @if($order->isPendingPayment() && $order->payment_deadline_at)
                <div class="p-4 rounded-xl bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-300/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Batas Waktu Pembayaran</span>
                            <div class="text-xs text-amber-900 mt-0.5">
                                Selesaikan pembayaran sebelum: <strong>{{ $order->payment_deadline_at->translatedFormat('d M Y, H:i') }} WITA</strong>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <span class="text-[11px] text-amber-700 font-semibold block">Sisa Waktu Pembayaran:</span>
                        <span class="text-lg font-black font-mono text-amber-900" id="paymentCountdownTimer">
                            -- : -- : --
                        </span>
                    </div>
                </div>
            @endif

            <!-- Customer & Fulfillment Information Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 text-xs">
                <!-- Customer Details (Masked Phone for Privacy) -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80 space-y-2">
                    <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px] block">Data Pemesan</span>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama:</span>
                        <strong class="text-slate-900">{{ $order->customer_name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">No. Kontak:</span>
                        @php
                            $phone = $order->customer_phone ?? '';
                            $maskedPhone = strlen($phone) > 6 
                                ? substr($phone, 0, 4) . '****' . substr($phone, -4) 
                                : $phone;
                        @endphp
                        <span class="text-slate-900 font-mono">{{ $maskedPhone }}</span>
                    </div>
                    @if($order->customer_email)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Email:</span>
                            <span class="text-slate-900">{{ $order->customer_email }}</span>
                        </div>
                    @endif
                </div>

                <!-- Fulfillment Details -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200/80 space-y-2">
                    <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px] block">Metode Pemenuhan & Pembayaran</span>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Pengambilan:</span>
                        <strong class="text-slate-900">{{ $order->fulfillment_method->label() }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Metode Bayar:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $order->isCod() ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $order->isCod() ? 'COD (Bayar di Tempat)' : 'Midtrans (Online)' }}
                        </span>
                    </div>
                    @if($order->fulfillment_method->value === 'pickup')
                        <div>
                            <span class="text-slate-500">Alamat Toko:</span>
                            <p class="text-slate-800 font-medium mt-0.5">{{ $storeSetting->business_name }} — {{ $storeSetting->address ?? 'Jl. Pengayoman, Makassar' }}</p>
                        </div>
                    @else
                        <div class="flex justify-between">
                            <span class="text-slate-500">Zona Kirim:</span>
                            <span class="text-slate-800 font-medium">{{ $order->deliveryZone->name ?? 'Pengiriman Reguler' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Alamat Pengantaran:</span>
                            <p class="text-slate-800 font-medium mt-0.5">{{ $order->delivery_address }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Items Table -->
            <div class="pt-4">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Rincian Paket Daging</h3>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold">
                            <tr>
                                <th class="py-3 px-4">Potongan Daging</th>
                                <th class="py-3 px-4 text-center">Kemasan</th>
                                <th class="py-3 px-4 text-center">Qty</th>
                                <th class="py-3 px-4 text-right">Harga Satuan</th>
                                <th class="py-3 px-4 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900">{{ $item->product_name_snapshot }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $item->product_sku_snapshot }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-center font-medium text-slate-700">
                                        {{ number_format($item->weight_per_package_gram) }} g
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-600">
                                        Rp {{ number_format($item->subtotal / $item->quantity, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/80 border-t border-slate-200 divide-y divide-slate-100 font-mono">
                            <tr>
                                <td colspan="4" class="py-2.5 px-4 text-right text-slate-600">Subtotal Belanja:</td>
                                <td class="py-2.5 px-4 text-right font-bold text-slate-800">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="py-2.5 px-4 text-right text-slate-600">Biaya Pengiriman (Ongkir):</td>
                                <td class="py-2.5 px-4 text-right font-bold text-slate-800">Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="bg-red-50/40 text-sm">
                                <td colspan="4" class="py-3 px-4 text-right font-bold text-red-900">TOTAL PEMBAYARAN:</td>
                                <td class="py-3 px-4 text-right font-black text-red-900 text-base">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Payment Button / Instructions Card -->
            @if($order->isCod())
                <div class="pt-2 text-center p-6 rounded-2xl bg-slate-900 text-white space-y-3" id="codActionCard">
                    <div class="w-12 h-12 mx-auto rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg">Metode Pembayaran: Bayar di Tempat (COD)</h3>
                    <div class="text-xs text-slate-300 max-w-md mx-auto space-y-1">
                        @if($order->isPaid())
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                ✓ Pembayaran Tunai Telah Selesai (Lunas)
                            </span>
                        @else
                            <p>Pesanan Anda sedang dipersiapkan oleh tim DONS MEAT.</p>
                            <p>Silakan siapkan uang tunai pas sebesar <strong class="text-amber-300 font-mono text-base">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong> untuk diserahkan kepada kurir pengantar saat paket daging sapi tiba.</p>
                        @endif
                    </div>
                </div>
            @elseif($order->isPendingPayment())
                <div class="pt-2 text-center p-6 rounded-2xl bg-slate-900 text-white space-y-3" id="paymentActionCard">
                    <h3 class="font-bold text-lg">Selesaikan Pembayaran Pesanan</h3>
                    <p class="text-xs text-slate-300 max-w-md mx-auto">
                        Total yang harus dibayar adalah <strong class="text-amber-300 font-mono text-sm">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>. Klik tombol di bawah untuk memilih metode pembayaran Midtrans Snap (QRIS, BCA, Mandiri, BNI, GoPay).
                    </p>
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <button type="button" 
                                id="payButton"
                                onclick="initiatePayment()"
                                class="inline-flex items-center px-8 py-3.5 rounded-xl bg-red-700 hover:bg-red-800 text-white font-extrabold text-sm sm:text-base shadow-lg transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Bayar Sekarang via Midtrans</span>
                        </button>
                        <button type="button" 
                                id="checkPaymentStatusBtn"
                                onclick="checkPaymentStatus()"
                                class="inline-flex items-center px-5 py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-semibold text-xs border border-slate-700 transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Perbarui Status</span>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400">Jika Anda telah menyelesaikan pembayaran namun status belum berubah, tekan "Perbarui Status" di atas.</p>
                </div>
            @endif

            <!-- Share / Bookmark Link Card -->
            <div class="p-4 rounded-xl bg-slate-100 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 text-slate-600">
                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <span>Simpan atau salin tautan pelacakan ini untuk memantau status pesanan kapan saja.</span>
                </div>
                <button type="button" 
                        onclick="copyTrackingLink()" 
                        id="copyLinkBtn"
                        class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 font-bold text-slate-700 hover:bg-slate-50 transition shrink-0">
                    Salin Tautan Pelacakan
                </button>
            </div>
        </div>

    </div>
</div>

@if($order->isPendingPayment())
    <script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
@endif

<script>
    // Copy tracking link helper
    function copyTrackingLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            const btn = document.getElementById('copyLinkBtn');
            btn.textContent = 'Tersalin!';
            setTimeout(() => {
                btn.textContent = 'Salin Tautan Pelacakan';
            }, 2000);
        });
    }

    // Payment initiator via Midtrans Snap popup
    function initiatePayment() {
        const payBtn = document.getElementById('payButton');
        if (!payBtn) return;

        payBtn.disabled = true;
        const originalHtml = payBtn.innerHTML;
        payBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Menyiapkan Pembayaran...</span>
        `;

        fetch("{{ route('order.snap_token', $order->public_tracking_token) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success || !data.snap_token) {
                throw new Error(data.message || 'Gagal memproses sesi pembayaran.');
            }

            if (window.snap) {
                window.snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        syncAndReload(result);
                    },
                    onPending: function(result) {
                        syncAndReload(result);
                    },
                    onError: function(result) {
                        alert('Pembayaran ditolak atau terjadi kendala pada transaksi.');
                        syncAndReload(result);
                    },
                    onClose: function() {
                        payBtn.disabled = false;
                        payBtn.innerHTML = originalHtml;
                    }
                });
            } else {
                alert('Pustaka Midtrans Snap gagal dimuat. Periksa koneksi internet Anda lalu coba lagi.');
                payBtn.disabled = false;
                payBtn.innerHTML = originalHtml;
            }
        })
        .catch(err => {
            alert(err.message || 'Terjadi kesalahan saat menghubungi server pembayaran.');
            payBtn.disabled = false;
            payBtn.innerHTML = originalHtml;
        });
    }

    // Sync status with server and reload
    function syncAndReload(result) {
        const payBtn = document.getElementById('payButton');
        if (payBtn) {
            payBtn.disabled = true;
            payBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Memverifikasi Pembayaran...</span>
            `;
        }

        fetch("{{ route('order.sync_status', $order->public_tracking_token) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({ result: result })
        })
        .finally(() => {
            window.location.reload();
        });
    }

    // Manual status check helper
    function checkPaymentStatus() {
        const checkBtn = document.getElementById('checkPaymentStatusBtn');
        if (!checkBtn) return;

        checkBtn.disabled = true;
        const origText = checkBtn.innerHTML;
        checkBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Memeriksa...</span>
        `;

        fetch("{{ route('order.sync_status', $order->public_tracking_token) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.is_paid) {
                window.location.reload();
            } else {
                alert(data.message || 'Pembayaran belum terdeteksi. Silakan selesaikan pembayaran di Midtrans terlebih dahulu.');
                checkBtn.disabled = false;
                checkBtn.innerHTML = origText;
            }
        })
        .catch(() => {
            alert('Gagal menghubungi server untuk verifikasi status.');
            checkBtn.disabled = false;
            checkBtn.innerHTML = origText;
        });
    }

    // Countdown Timer
    @if($order->isPendingPayment() && $order->payment_deadline_at)
        const deadline = new Date("{{ $order->payment_deadline_at->toIso8601String() }}").getTime();
        const timerEl = document.getElementById('paymentCountdownTimer');

        const interval = setInterval(() => {
            const now = new Date().getTime();
            const diff = deadline - now;

            if (diff <= 0) {
                clearInterval(interval);
                timerEl.textContent = 'Waktu Habis';
                timerEl.classList.add('text-red-600');
                return;
            }

            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            const pad = (n) => n < 10 ? '0' + n : n;
            timerEl.textContent = `${pad(hours)} : ${pad(minutes)} : ${pad(seconds)}`;
        }, 1000);
    @endif
</script>
@endsection
