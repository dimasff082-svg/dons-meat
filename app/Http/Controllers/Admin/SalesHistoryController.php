<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderChannel;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesHistoryController extends Controller
{
    /**
     * Riwayat Penjualan Keseluruhan (Untuk Role Pemilik dan Admin).
     */
    public function index(Request $request): View
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->string('start_date')->value())->startOfDay()->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->string('end_date')->value())->endOfDay()->toDateString()
            : Carbon::now()->toDateString();

        $query = Order::query()
            ->with(['items', 'createdByUser', 'deliveryZone', 'payments'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->latest('id');

        // Filter by Channel
        if ($request->filled('channel') && in_array($request->string('channel')->value(), ['online', 'in_store'], true)) {
            $query->where('channel', $request->string('channel')->value());
        }

        // Filter by Payment Status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status')->value());
        }

        // Filter by specific Cashier / Staff
        if ($request->filled('cashier_id')) {
            $cashierId = (int) $request->input('cashier_id');
            $query->where(function ($q) use ($cashierId) {
                $q->where('created_by_user_id', $cashierId)
                    ->orWhereHas('statusHistories', function ($sub) use ($cashierId) {
                        $sub->where('actor_user_id', $cashierId);
                    });
            });
        }

        // Search by Code, Customer Name, or Phone
        if ($request->filled('search')) {
            $search = trim($request->string('search')->value());
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        // Clone query for metrics before pagination
        $baseMetricsQuery = clone $query;

        $totalOrdersCount = (int) $baseMetricsQuery->count();
        $totalRevenue = (float) (clone $baseMetricsQuery)
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total_amount');

        // Total meat weight sold (in grams)
        $orderIds = (clone $baseMetricsQuery)->pluck('id');
        $totalWeightGram = DB::table('order_items')
            ->whereIn('order_id', $orderIds)
            ->sum('total_weight_gram');

        // Today's overall revenue
        $todayRevenue = (float) Order::where('payment_status', PaymentStatus::Paid)
            ->whereDate('created_at', today())
            ->sum('total_amount');

        $todayOrdersCount = (int) Order::whereDate('created_at', today())->count();

        $orders = $query->paginate(20)->withQueryString();

        // Cashiers / Staff list for dropdown filter
        $staffMembers = User::whereIn('role', ['cashier', 'admin'])->orderBy('name')->get();

        return view('admin.sales_history.index', compact(
            'orders',
            'startDate',
            'endDate',
            'totalOrdersCount',
            'totalRevenue',
            'totalWeightGram',
            'todayRevenue',
            'todayOrdersCount',
            'staffMembers'
        ));
    }

    /**
     * Export Riwayat Seluruh Penjualan ke CSV.
     */
    public function exportAllCsv(Request $request): StreamedResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->string('start_date')->value())->startOfDay()->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->string('end_date')->value())->endOfDay()->toDateString()
            : Carbon::now()->toDateString();

        $query = Order::query()
            ->with(['items', 'createdByUser', 'deliveryZone'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->latest('id');

        if ($request->filled('channel') && in_array($request->string('channel')->value(), ['online', 'in_store'], true)) {
            $query->where('channel', $request->string('channel')->value());
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status')->value());
        }

        if ($request->filled('cashier_id')) {
            $cashierId = (int) $request->input('cashier_id');
            $query->where(function ($q) use ($cashierId) {
                $q->where('created_by_user_id', $cashierId)
                    ->orWhereHas('statusHistories', function ($sub) use ($cashierId) {
                        $sub->where('actor_user_id', $cashierId);
                    });
            });
        }

        $filename = 'riwayat-penjualan-dons-meat-'.$startDate.'-sd-'.$endDate.'.csv';

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($handle, [
                'No. Pesanan',
                'Waktu Transaksi',
                'Kanal Penjualan',
                'Nama Pelanggan',
                'No. HP',
                'Petugas / Kasir',
                'Metode Pemenuhan',
                'Total Berat (Gram)',
                'Subtotal (Rp)',
                'Ongkir (Rp)',
                'Total Transaksi (Rp)',
                'Status Pembayaran',
                'Status Pesanan',
            ]);

            $query->chunk(200, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    $totalWeight = $order->items->sum('total_weight_gram');

                    fputcsv($handle, [
                        $order->order_code,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->channel?->label() ?? $order->channel,
                        $order->customer_name,
                        $order->customer_phone,
                        $order->createdByUser->name ?? 'Online / Sistem',
                        $order->fulfillment_method?->label() ?? '-',
                        $totalWeight,
                        $order->subtotal,
                        $order->delivery_fee,
                        $order->total_amount,
                        $order->payment_status?->label() ?? $order->payment_status,
                        $order->order_status?->label() ?? $order->order_status,
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Riwayat Penjualan Khusus Role Kasir (Yang dilayani oleh kasir yang sedang login).
     */
    public function myHistory(Request $request): View
    {
        $cashierId = auth()->id();

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->string('start_date')->value())->startOfDay()->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->string('end_date')->value())->endOfDay()->toDateString()
            : Carbon::now()->toDateString();

        // Orders handled by this cashier:
        // 1. In-Store direct sales created by this cashier (created_by_user_id)
        // 2. Online orders processed/fulfilled by this cashier (statusHistories actor)
        $query = Order::query()
            ->with(['items', 'createdByUser', 'deliveryZone', 'payments'])
            ->where(function ($q) use ($cashierId) {
                $q->where('created_by_user_id', $cashierId)
                    ->orWhereHas('statusHistories', function ($sub) use ($cashierId) {
                        $sub->where('actor_user_id', $cashierId);
                    })
                    ->orWhereHas('stockMovements', function ($sub) use ($cashierId) {
                        $sub->where('created_by_user_id', $cashierId);
                    });
            })
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->latest('id');

        // Filter by service type (Kanal: In-Store vs Online)
        if ($request->filled('channel') && in_array($request->string('channel')->value(), ['online', 'in_store'], true)) {
            $query->where('channel', $request->string('channel')->value());
        }

        // Search by Code or Customer Name
        if ($request->filled('search')) {
            $search = trim($request->string('search')->value());
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $baseMetricsQuery = clone $query;

        $myTotalOrders = (int) $baseMetricsQuery->count();
        $myTotalRevenue = (float) (clone $baseMetricsQuery)
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total_amount');

        $orderIds = (clone $baseMetricsQuery)->pluck('id');
        $myTotalWeightGram = DB::table('order_items')
            ->whereIn('order_id', $orderIds)
            ->sum('total_weight_gram');

        // Today's metrics for this cashier
        $todayServedQuery = Order::where(function ($q) use ($cashierId) {
            $q->where('created_by_user_id', $cashierId)
                ->orWhereHas('statusHistories', function ($sub) use ($cashierId) {
                    $sub->where('actor_user_id', $cashierId);
                });
        })->whereDate('created_at', today());

        $myTodayOrders = (int) (clone $todayServedQuery)->count();
        $myTodayRevenue = (float) (clone $todayServedQuery)
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total_amount');

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.sales_history.cashier', compact(
            'orders',
            'startDate',
            'endDate',
            'myTotalOrders',
            'myTotalRevenue',
            'myTotalWeightGram',
            'myTodayOrders',
            'myTodayRevenue'
        ));
    }

    /**
     * Export Riwayat Penjualan Kasir Pribadi ke CSV.
     */
    public function exportMyCsv(Request $request): StreamedResponse
    {
        $cashierId = auth()->id();
        $cashierName = auth()->user()->name ?? 'Kasir';

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->string('start_date')->value())->startOfDay()->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->string('end_date')->value())->endOfDay()->toDateString()
            : Carbon::now()->toDateString();

        $query = Order::query()
            ->with(['items', 'createdByUser', 'deliveryZone'])
            ->where(function ($q) use ($cashierId) {
                $q->where('created_by_user_id', $cashierId)
                    ->orWhereHas('statusHistories', function ($sub) use ($cashierId) {
                        $sub->where('actor_user_id', $cashierId);
                    })
                    ->orWhereHas('stockMovements', function ($sub) use ($cashierId) {
                        $sub->where('created_by_user_id', $cashierId);
                    });
            })
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->latest('id');

        if ($request->filled('channel') && in_array($request->string('channel')->value(), ['online', 'in_store'], true)) {
            $query->where('channel', $request->string('channel')->value());
        }

        $cleanCashierSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $cashierName));
        $filename = "riwayat-penjualan-{$cleanCashierSlug}-{$startDate}-sd-{$endDate}.csv";

        return response()->stream(function () use ($query, $cashierName) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($handle, [
                'No. Pesanan',
                'Waktu Transaksi',
                'Tipe Layanan',
                'Nama Pelanggan',
                'No. HP',
                'Kasir / Petugas',
                'Total Berat (Gram)',
                'Total Transaksi (Rp)',
                'Status Pembayaran',
                'Status Pesanan',
            ]);

            $query->chunk(200, function ($orders) use ($handle, $cashierName) {
                foreach ($orders as $order) {
                    $totalWeight = $order->items->sum('total_weight_gram');
                    $serviceType = $order->channel === OrderChannel::InStore
                        ? 'Penjualan Langsung Toko'
                        : 'Pesanan Masuk ('.($order->fulfillment_method?->label() ?? 'Online').')';

                    fputcsv($handle, [
                        $order->order_code,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $serviceType,
                        $order->customer_name,
                        $order->customer_phone,
                        $cashierName,
                        $totalWeight,
                        $order->total_amount,
                        $order->payment_status?->label() ?? $order->payment_status,
                        $order->order_status?->label() ?? $order->order_status,
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
