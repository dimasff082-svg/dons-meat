@extends('layouts.admin')

@section('title', 'Pesanan Masuk')
@section('page-title', 'Daftar Pesanan')

@section('content')
<div class="space-y-6">

    <!-- Top Action / Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Manajemen Pesanan</h2>
            <p class="text-xs text-slate-500 mt-1">Pantau dan proses pesanan masuk dari kanal online maupun penjualan langsung di toko.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.direct-sales.create') }}" 
               class="inline-flex items-center px-4 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white font-semibold text-xs transition shadow-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                + Penjualan Langsung
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
        <!-- Quick Status Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-100 text-xs font-semibold">
            <a href="{{ route('admin.orders.index', request()->except(['status', 'page'])) }}" 
               class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua ({{ $counters['all'] }})
            </a>
            <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'processing'])) }}" 
               class="px-3 py-1.5 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 {{ request('status') === 'processing' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' }}">
                <span class="w-2 h-2 rounded-full bg-amber-500 {{ $counters['processing'] > 0 ? 'animate-ping' : '' }}"></span>
                Perlu Disiapkan ({{ $counters['processing'] }})
            </a>
            <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'pending_payment'])) }}" 
               class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ request('status') === 'pending_payment' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                Menunggu Bayar ({{ $counters['pending_payment'] }})
            </a>
            <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'ready_for_pickup'])) }}" 
               class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ request('status') === 'ready_for_pickup' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100' }}">
                Siap Diambil ({{ $counters['ready_for_pickup'] }})
            </a>
            <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'on_delivery'])) }}" 
               class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ request('status') === 'on_delivery' ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100' }}">
                Dalam Pengiriman ({{ $counters['on_delivery'] }})
            </a>
            <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'completed'])) }}" 
               class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ request('status') === 'completed' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                Selesai ({{ $counters['completed'] }})
            </a>
            <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'cancelled'])) }}" 
               class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ request('status') === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                Dibatalkan ({{ $counters['cancelled'] }})
            </a>
            <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => 'expired'])) }}" 
               class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ request('status') === 'expired' ? 'bg-slate-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Kedaluwarsa ({{ $counters['expired'] }})
            </a>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-1">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <!-- Search -->
            <div class="lg:col-span-2">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari kode order, nama pemesan, no HP..." 
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Channel Filter -->
            <div>
                <select name="channel" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Semua Kanal</option>
                    <option value="online" {{ request('channel') === 'online' ? 'selected' : '' }}>Online Website</option>
                    <option value="in_store" {{ request('channel') === 'in_store' ? 'selected' : '' }}>Penjualan Langsung Toko</option>
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div>
                <select name="payment_status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Semua Pembayaran</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Dibayar (Unpaid)</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending Gateway</option>
                    <option value="expired" {{ request('payment_status') === 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Gagal</option>
                </select>
            </div>

            <!-- Submit & Reset Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition">
                    Filter
                </button>
                <a href="{{ route('admin.orders.index') }}" class="py-2 px-3 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-200 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($orders->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm font-semibold text-slate-600">Tidak ada pesanan yang sesuai dengan filter.</p>
                <p class="text-xs mt-1 text-slate-400">Coba ubah kata kunci pencarian atau bersihkan filter.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-5 py-3.5 text-left">Kode & Waktu</th>
                            <th class="px-4 py-3.5 text-left">Pemesan</th>
                            <th class="px-4 py-3.5 text-left">Kanal & Pemenuhan</th>
                            <th class="px-4 py-3.5 text-right">Total Tagihan</th>
                            <th class="px-4 py-3.5 text-center">Status Pembayaran</th>
                            <th class="px-4 py-3.5 text-center">Status Pesanan</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($orders as $order)
                            @php
                                $orderBadge = match($order->order_status->value ?? $order->order_status) {
                                    'pending_payment' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                    'processing' => 'bg-amber-100 text-amber-800 border border-amber-300 font-bold',
                                    'ready_for_pickup' => 'bg-purple-100 text-purple-800 border border-purple-200 font-bold',
                                    'on_delivery' => 'bg-indigo-100 text-indigo-800 border border-indigo-200 font-bold',
                                    'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                    'cancelled' => 'bg-rose-50 text-rose-700 border border-rose-200',
                                    'expired' => 'bg-slate-100 text-slate-600 border border-slate-300',
                                    default => 'bg-slate-100 text-slate-700',
                                };

                                $paymentBadge = match($order->payment_status->value ?? $order->payment_status) {
                                    'paid' => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
                                    'unpaid' => 'bg-amber-50 text-amber-800 border border-amber-200',
                                    'pending' => 'bg-blue-50 text-blue-800 border border-blue-200',
                                    'expired' => 'bg-slate-100 text-slate-600 border border-slate-300',
                                    'failed' => 'bg-rose-100 text-rose-800 border border-rose-300',
                                    'cancelled' => 'bg-slate-100 text-slate-600 border border-slate-300',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Order Code & Date -->
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-slate-900 hover:text-red-700 text-sm block">
                                        {{ $order->order_code }}
                                    </a>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">
                                        {{ $order->created_at->translatedFormat('d M Y, H:i') }} WITA
                                    </span>
                                </td>

                                <!-- Customer Details -->
                                <td class="px-4 py-3.5">
                                    <span class="font-bold text-slate-800 block text-xs">{{ $order->customer_name }}</span>
                                    <span class="text-slate-500 text-[11px] block">{{ $order->customer_phone }}</span>
                                </td>

                                <!-- Channel & Fulfillment -->
                                <td class="px-4 py-3.5 space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $order->channel === \App\Enums\OrderChannel::Online ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                            {{ $order->channel?->label() }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $order->fulfillment_method === \App\Enums\FulfillmentMethod::Delivery ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $order->fulfillment_method?->label() }}
                                        </span>
                                    </div>
                                    @if($order->deliveryZone)
                                        <span class="text-[10px] text-slate-500 block truncate max-w-[150px]">
                                            Zona: {{ $order->deliveryZone->name }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Total -->
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900 text-sm">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    <span class="text-[10px] text-slate-400 block font-normal">
                                        {{ $order->items->count() }} item paket
                                    </span>
                                </td>

                                <!-- Payment Status -->
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $paymentBadge }}">
                                        {{ $order->payment_status?->label() }}
                                    </span>
                                </td>

                                <!-- Order Status -->
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] {{ $orderBadge }}">
                                        {{ $order->order_status?->label() }}
                                    </span>
                                </td>

                                <!-- Action Link -->
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                        Detail & Proses
                                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection

