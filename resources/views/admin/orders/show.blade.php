@extends('layouts.admin')

@section('title', 'Detail Pesanan ' . $order->order_code)
@section('page-title', 'Detail Pesanan')

@section('content')
<div class="space-y-6">

    <!-- Top Navigation / Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <nav class="flex items-center space-x-2 text-xs text-slate-500">
            <a href="{{ route('admin.orders.index') }}" class="hover:text-red-700">Daftar Pesanan</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold font-mono">{{ $order->order_code }}</span>
        </nav>

        <div class="flex items-center gap-2">
            @if($order->channel === \App\Enums\OrderChannel::Online)
                <a href="{{ route('order.track', $order->public_tracking_token) }}" 
                   target="_blank"
                   class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition shadow-sm">
                    <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Halaman Lacak Publik
                </a>
            @endif
            <a href="{{ route('admin.orders.index') }}" 
               class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Order Header & Status Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kode Pesanan</span>
                <h1 class="text-2xl sm:text-3xl font-black font-mono text-slate-900 tracking-tight">{{ $order->order_code }}</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Dibuat pada {{ $order->created_at->translatedFormat('d F Y, H:i') }} WITA 
                    &bull; Kanal: <strong class="text-slate-700">{{ $order->channel?->label() }}</strong>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @php
                    $orderBadge = match($order->order_status->value ?? $order->order_status) {
                        'pending_payment' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'processing' => 'bg-amber-100 text-amber-800 border-amber-300',
                        'ready_for_pickup' => 'bg-purple-100 text-purple-800 border-purple-200',
                        'on_delivery' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                        'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        'cancelled' => 'bg-rose-100 text-rose-800 border-rose-300',
                        'expired' => 'bg-slate-100 text-slate-600 border-slate-300',
                        default => 'bg-slate-100 text-slate-700',
                    };

                    $paymentBadge = match($order->payment_status->value ?? $order->payment_status) {
                        'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        'unpaid' => 'bg-amber-50 text-amber-800 border-amber-200',
                        'pending' => 'bg-blue-50 text-blue-800 border-blue-200',
                        'expired' => 'bg-slate-100 text-slate-600 border-slate-300',
                        'failed' => 'bg-rose-100 text-rose-800 border-rose-300',
                        'cancelled' => 'bg-slate-100 text-slate-600 border-slate-300',
                        default => 'bg-slate-100 text-slate-700',
                    };
                @endphp

                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $orderBadge }}">
                    Status Pesanan: {{ $order->order_status?->label() }}
                </span>

                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $paymentBadge }}">
                    Pembayaran: {{ $order->payment_status?->label() }}
                </span>
            </div>
        </div>

        <!-- Operational Action Panel (Fulfillment & Cancellation) -->
        <div class="bg-slate-50 p-5 rounded-xl border border-slate-200/90 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Tindakan Pemrosesan Pesanan</h3>

            <!-- 1. Processing State Actions -->
            @if($order->order_status === \App\Enums\OrderStatus::Processing)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-amber-50 p-4 rounded-xl border border-amber-200 text-amber-900">
                    <div>
                        <p class="text-xs font-bold">Pesanan telah lunas dan siap diproses.</p>
                        <p class="text-[11px] text-amber-800 mt-0.5">
                            Metode pemenuhan: <strong>{{ $order->fulfillment_method?->label() }}</strong>. 
                            Siapkan item daging sesuai pesanan lalu perbarui status di bawah.
                        </p>
                    </div>

                    @if($order->fulfillment_method === \App\Enums\FulfillmentMethod::Pickup)
                        <form method="POST" action="{{ route('admin.orders.fulfillment', $order) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="order_status" value="ready_for_pickup">
                            <button type="submit" class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-xs font-bold transition shadow-sm whitespace-nowrap">
                                &check; Tandai Siap Diambil di Toko
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.orders.fulfillment', $order) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="order_status" value="on_delivery">
                            <button type="submit" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white rounded-xl text-xs font-bold transition shadow-sm whitespace-nowrap">
                                &check; Serahkan ke Kurir (Dalam Pengiriman)
                            </button>
                        </form>
                    @endif
                </div>

            <!-- 2. Ready for Pickup State Actions -->
            @elseif($order->order_status === \App\Enums\OrderStatus::ReadyForPickup)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-purple-50 p-4 rounded-xl border border-purple-200 text-purple-900">
                    <div>
                        <p class="text-xs font-bold">Pesanan sedang menunggu pengambilan oleh pelanggan di toko.</p>
                        <p class="text-[11px] text-purple-800 mt-0.5">Setelah pesanan diserahkan ke pelanggan, tandai pesanan sebagai selesai.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.orders.fulfillment', $order) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="order_status" value="completed">
                        <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition shadow-sm whitespace-nowrap">
                            &check; Tandai Selesai (Diserahkan ke Pelanggan)
                        </button>
                    </form>
                </div>

            <!-- 3. On Delivery State Actions -->
            @elseif($order->order_status === \App\Enums\OrderStatus::OnDelivery)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-indigo-50 p-4 rounded-xl border border-indigo-200 text-indigo-900">
                    <div>
                        <p class="text-xs font-bold">Pesanan sedang diantar oleh kurir ke alamat tujuan.</p>
                        <p class="text-[11px] text-indigo-800 mt-0.5">Setelah kurir mengonfirmasi pesanan telah tiba dan diterima pelanggan, tandai pesanan sebagai selesai.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.orders.fulfillment', $order) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="order_status" value="completed">
                        <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition shadow-sm whitespace-nowrap">
                            &check; Tandai Selesai (Diterima Pelanggan)
                        </button>
                    </form>
                </div>

            <!-- 4. Completed State Info -->
            @elseif($order->order_status === \App\Enums\OrderStatus::Completed)
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Pesanan telah <strong>selesai</strong> diserahkan kepada pelanggan pada <strong>{{ $order->completed_at ? $order->completed_at->translatedFormat('d M Y, H:i') : '-' }} WITA</strong>.</span>
                    </div>
                </div>

            <!-- 5. Pending Payment State Actions -->
            @elseif($order->order_status === \App\Enums\OrderStatus::PendingPayment)
                <div class="space-y-3">
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs">
                        Pesanan ini masih menunggu konfirmasi pembayaran Midtrans dari pelanggan. Batas waktu bayar: <strong>{{ $order->payment_deadline_at ? $order->payment_deadline_at->translatedFormat('d M Y, H:i') : '-' }} WITA</strong>.
                    </div>

                    @if($order->canBeCancelled())
                        <!-- Manual Cancellation Form for Unpaid Orders -->
                        <div class="pt-2 border-t border-slate-200">
                            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok cadangan akan otomatis dilepaskan.');" class="flex flex-col sm:flex-row items-end sm:items-center gap-2">
                                @csrf
                                <div class="flex-1 w-full">
                                    <input type="text" name="cancellation_reason" placeholder="Alasan pembatalan operasional (opsional)..." class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300">
                                </div>
                                <button type="submit" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shrink-0">
                                    Batalkan Pesanan
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

            <!-- 6. Cancelled / Expired State Info -->
            @else
                <div class="p-4 rounded-xl bg-slate-100 border border-slate-300 text-slate-700 text-xs">
                    Pesanan ini berstatus <strong>{{ $order->order_status?->label() }}</strong>. Reservasi stok untuk pesanan ini telah dilepaskan kembali ke stok persediaan toko.
                </div>
            @endif

            <!-- PRD Strict Business Rule Notice -->
            @if($order->isPaid())
                <div class="text-[11px] text-slate-400 italic">
                    * Sesuai ketentuan PRD DONS MEAT, pesanan yang berstatus lunas tidak menyediakan pembatalan melalui alur sistem.
                </div>
            @endif
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Item Snapshot & Customer/Delivery Info (Span 2) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Item Snapshot Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Rincian Item Daging (Snapshot)</h3>
                    <span class="text-xs text-slate-500">{{ $order->items->count() }} Variasi Paket</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold">
                            <tr>
                                <th class="px-3 py-2.5 text-left">Produk & SKU</th>
                                <th class="px-3 py-2.5 text-center">Paket</th>
                                <th class="px-3 py-2.5 text-center">Qty</th>
                                <th class="px-3 py-2.5 text-center">Total Berat</th>
                                <th class="px-3 py-2.5 text-right">Harga Snapshot/kg</th>
                                <th class="px-3 py-2.5 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-3 py-3">
                                        <strong class="text-slate-900 block">{{ $item->product_name_snapshot }}</strong>
                                        <span class="text-[11px] text-slate-400 font-mono">{{ $item->product_sku_snapshot }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center text-slate-700">
                                        {{ number_format($item->weight_per_package_gram, 0, ',', '.') }} g
                                    </td>
                                    <td class="px-3 py-3 text-center font-bold text-slate-900">
                                        {{ $item->quantity }}x
                                    </td>
                                    <td class="px-3 py-3 text-center text-slate-700 font-semibold">
                                        {{ number_format($item->total_weight_gram, 0, ',', '.') }} g
                                        <span class="text-[10px] text-slate-400 block font-normal">({{ number_format($item->total_weight_gram / 1000, 2, ',', '.') }} kg)</span>
                                    </td>
                                    <td class="px-3 py-3 text-right text-slate-600 font-mono">
                                        Rp {{ number_format($item->price_per_kg_snapshot, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 text-right font-mono font-bold text-slate-900">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/70 divide-y divide-slate-200 text-slate-700">
                            <tr>
                                <td colspan="5" class="px-3 py-2 text-right font-semibold">Subtotal:</td>
                                <td class="px-3 py-2 text-right font-mono font-bold text-slate-900">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="px-3 py-2 text-right font-semibold">Ongkos Kirim ({{ $order->fulfillment_method?->label() }}):</td>
                                <td class="px-3 py-2 text-right font-mono font-bold text-slate-900">Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="text-sm bg-slate-100 font-bold">
                                <td colspan="5" class="px-3 py-2.5 text-right text-slate-900 uppercase tracking-wider text-xs">Total Akhir:</td>
                                <td class="px-3 py-2.5 text-right font-mono text-red-700 text-base">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Customer & Fulfillment Information Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Pelanggan & Pengiriman</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <!-- Customer Contact Info -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                        <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px] block">Kontak Pelanggan</span>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nama:</span>
                            <strong class="text-slate-900">{{ $order->customer_name }}</strong>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Telepon/WhatsApp:</span>
                            <div class="flex items-center gap-1.5">
                                <strong class="text-slate-900 font-mono">{{ $order->customer_phone }}</strong>
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $order->customer_phone);
                                    if (str_starts_with($cleanPhone, '0')) {
                                        $cleanPhone = '62' . substr($cleanPhone, 1);
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="p-1 text-emerald-600 hover:text-emerald-700" title="Hubungi via WhatsApp">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Email:</span>
                            <span class="text-slate-800">{{ $order->customer_email ?: '-' }}</span>
                        </div>
                        @if($order->customer_note)
                            <div class="pt-2 border-t border-slate-200">
                                <span class="text-slate-500 block">Catatan Pemesan:</span>
                                <p class="text-slate-800 italic mt-0.5">{{ $order->customer_note }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Fulfillment & Delivery Info -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                        <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px] block">Metode Pemenuhan</span>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Metode:</span>
                            <strong class="text-slate-900">{{ $order->fulfillment_method?->label() }}</strong>
                        </div>

                        @if($order->fulfillment_method === \App\Enums\FulfillmentMethod::Delivery)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Zona Pengiriman:</span>
                                <strong class="text-slate-900">{{ $order->deliveryZone ? $order->deliveryZone->name : '-' }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Tarif Zona:</span>
                                <span class="text-slate-900 font-mono font-semibold">Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</span>
                            </div>
                            <div class="pt-2 border-t border-slate-200">
                                <span class="text-slate-500 block">Alamat Pengantaran:</span>
                                <p class="text-slate-800 mt-0.5 leading-relaxed">{{ $order->delivery_address ?: '-' }}</p>
                            </div>
                        @else
                            <div class="pt-2 border-t border-slate-200 text-slate-600">
                                <p>Pesanan diambil langsung di gerai toko DONS MEAT.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Payment Details & Status Timeline (Span 1) -->
        <div class="space-y-6">

            <!-- Payment Details Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Pembayaran</h3>

                <dl class="space-y-2.5 text-xs">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Penyedia:</dt>
                        <dd class="font-bold text-slate-900 uppercase">{{ $order->payment ? $order->payment->provider : ($order->channel === \App\Enums\OrderChannel::InStore ? 'Tunai / Kasir' : 'Midtrans') }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-slate-500">Status Pembayaran:</dt>
                        <dd>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $paymentBadge }}">
                                {{ $order->payment_status?->label() }}
                            </span>
                        </dd>
                    </div>

                    @if($order->payment)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Tipe Pembayaran:</dt>
                            <dd class="font-mono text-slate-800">{{ $order->payment->payment_type ?: '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">ID Transaksi Gateway:</dt>
                            <dd class="font-mono text-slate-800 text-[11px] truncate max-w-[140px]" title="{{ $order->payment->gateway_transaction_id }}">
                                {{ $order->payment->gateway_transaction_id ?: '-' }}
                            </dd>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <dt class="text-slate-500">Waktu Lunas:</dt>
                        <dd class="text-slate-800">{{ $order->paid_at ? $order->paid_at->translatedFormat('d M Y, H:i') : '-' }}</dd>
                    </div>

                    @if($order->payment_deadline_at && $order->isPendingPayment())
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Batas Waktu Bayar:</dt>
                            <dd class="text-amber-800 font-semibold">{{ $order->payment_deadline_at->translatedFormat('d M Y, H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Status Timeline Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Riwayat Status (Audit Trail)</h3>

                @if($order->statusHistories->isEmpty())
                    <p class="text-xs text-slate-400">Belum ada catatan riwayat perubahan status.</p>
                @else
                    <ol class="relative border-l border-slate-200 ml-3 space-y-4 text-xs">
                        @foreach($order->statusHistories as $history)
                            <li class="mb-2 ml-4">
                                <div class="absolute w-2.5 h-2.5 bg-red-600 rounded-full -left-1.5 border border-white"></div>
                                <time class="mb-1 text-[11px] font-normal leading-none text-slate-400 block">
                                    {{ $history->created_at->translatedFormat('d M Y, H:i') }} WITA
                                </time>
                                <p class="text-xs font-semibold text-slate-900">
                                    Menjadi: <span class="text-red-700 uppercase tracking-wide">{{ $history->to_status }}</span>
                                </p>
                                @if($history->note)
                                    <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed bg-slate-50 p-2 rounded-lg border border-slate-100">
                                        {{ $history->note }}
                                    </p>
                                @endif
                                <span class="text-[10px] text-slate-400 mt-0.5 block">
                                    Sumber: {{ $history->source }} {{ $history->actorUser ? '(' . $history->actorUser->name . ')' : '' }}
                                </span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection

