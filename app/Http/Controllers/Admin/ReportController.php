<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderChannel;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display Sales & Revenue report with daily breakdown, channel comparison, and transaction listing.
     */
    public function sales(Request $request): View
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->string('start_date')->value())->startOfDay()->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->string('end_date')->value())->endOfDay()->toDateString()
            : Carbon::now()->toDateString();

        $selectedChannel = $request->filled('channel') && in_array($request->string('channel')->value(), ['online', 'in_store'], true)
            ? $request->string('channel')->value()
            : null;

        // Base query for paid transactions within the date range
        $paidQuery = Order::query()
            ->where('payment_status', PaymentStatus::Paid)
            ->whereBetween(DB::raw('DATE(COALESCE(paid_at, created_at))'), [$startDate, $endDate]);

        // Single aggregated query for all sales metrics
        $metrics = (clone $paidQuery)
            ->selectRaw('
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN channel = "online" THEN total_amount ELSE 0 END), 0) as online_revenue,
                COALESCE(SUM(CASE WHEN channel = "in_store" THEN total_amount ELSE 0 END), 0) as in_store_revenue,
                COUNT(id) as total_paid_orders,
                COALESCE(SUM(CASE WHEN channel = "online" THEN 1 ELSE 0 END), 0) as online_paid_orders,
                COALESCE(SUM(CASE WHEN channel = "in_store" THEN 1 ELSE 0 END), 0) as in_store_paid_orders
            ')
            ->first();

        $totalRevenue = (float) ($metrics->total_revenue ?? 0);
        $onlineRevenue = (float) ($metrics->online_revenue ?? 0);
        $inStoreRevenue = (float) ($metrics->in_store_revenue ?? 0);

        $totalPaidOrders = (int) ($metrics->total_paid_orders ?? 0);
        $onlinePaidOrders = (int) ($metrics->online_paid_orders ?? 0);
        $inStorePaidOrders = (int) ($metrics->in_store_paid_orders ?? 0);

        $averageOrderValue = $totalPaidOrders > 0 ? $totalRevenue / $totalPaidOrders : 0.0;

        // Daily breakdown aggregation for the selected period
        $dailyBreakdown = Order::query()
            ->where('payment_status', PaymentStatus::Paid)
            ->whereBetween(DB::raw('DATE(COALESCE(paid_at, created_at))'), [$startDate, $endDate])
            ->selectRaw('DATE(COALESCE(paid_at, created_at)) as report_date,
                         SUM(CASE WHEN channel = "online" THEN total_amount ELSE 0 END) as online_total,
                         SUM(CASE WHEN channel = "in_store" THEN total_amount ELSE 0 END) as instore_total,
                         SUM(total_amount) as daily_total,
                         COUNT(id) as total_count')
            ->groupBy('report_date')
            ->orderBy('report_date', 'desc')
            ->get();

        // Transaction list query (can filter by channel as well)
        $transactionsQuery = Order::query()
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->with(['items', 'deliveryZone'])
            ->latest('id');

        if ($selectedChannel) {
            $transactionsQuery->where('channel', $selectedChannel);
        }

        if ($request->filled('payment_status')) {
            $transactionsQuery->where('payment_status', $request->string('payment_status')->value());
        }

        $transactions = $transactionsQuery->paginate(20)->withQueryString();

        return view('admin.reports.sales', compact(
            'startDate',
            'endDate',
            'selectedChannel',
            'totalRevenue',
            'onlineRevenue',
            'inStoreRevenue',
            'totalPaidOrders',
            'onlinePaidOrders',
            'inStorePaidOrders',
            'averageOrderValue',
            'dailyBreakdown',
            'transactions'
        ));
    }

    /**
     * Export sales report transactions to CSV.
     */
    public function exportSalesCsv(Request $request): StreamedResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->string('start_date')->value())->startOfDay()->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->string('end_date')->value())->endOfDay()->toDateString()
            : Carbon::now()->toDateString();

        $selectedChannel = $request->filled('channel') && in_array($request->string('channel')->value(), ['online', 'in_store'], true)
            ? $request->string('channel')->value()
            : null;

        $fileName = "laporan-penjualan-DONS-MEAT-{$startDate}-sd-{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($startDate, $endDate, $selectedChannel) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");

            // CSV Header Row
            fputcsv($file, [
                'Kode Pesanan',
                'Tanggal Dibuat',
                'Tanggal Bayar',
                'Kanal Penjualan',
                'Metode Pemenuhan',
                'Nama Pelanggan',
                'Nomor Telepon',
                'Status Pembayaran',
                'Status Pesanan',
                'Subtotal (Rp)',
                'Ongkos Kirim (Rp)',
                'Total Akhir (Rp)',
            ]);

            $query = Order::query()
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->latest('id');

            if ($selectedChannel) {
                $query->where('channel', $selectedChannel);
            }

            $query->chunk(200, function ($orders) use ($file) {
                foreach ($orders as $order) {
                    fputcsv($file, [
                        $order->order_code,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : '-',
                        $order->channel?->label() ?? $order->channel,
                        $order->fulfillment_method?->label() ?? $order->fulfillment_method,
                        $order->customer_name,
                        $order->customer_phone,
                        $order->payment_status?->label() ?? $order->payment_status,
                        $order->order_status?->label() ?? $order->order_status,
                        (float) $order->subtotal,
                        (float) $order->delivery_fee,
                        (float) $order->total_amount,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    /**
     * Display low stock report alert list.
     */
    public function lowStock(Request $request): View
    {
        $products = Product::with('category')
            ->whereRaw('(stock_gram - reserved_stock_gram) <= low_stock_threshold_gram')
            ->orderByRaw('(stock_gram - reserved_stock_gram) ASC')
            ->get();

        return view('admin.reports.low_stock', compact('products'));
    }

    /**
     * Export low stock report products to CSV for restocking reference.
     */
    public function exportLowStockCsv(Request $request): StreamedResponse
    {
        $fileName = 'laporan-stok-menipis-dons-meat-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'SKU Produk',
                'Nama Produk Daging',
                'Kategori',
                'Stok Fisik (gram)',
                'Stok Fisik (kg)',
                'Stok Dicadangkan (gram)',
                'Stok Tersedia (gram)',
                'Stok Tersedia (kg)',
                'Batas Minimum (gram)',
                'Batas Minimum (kg)',
                'Kebutuhan Restock (kg)',
                'Status Persediaan',
            ]);

            $products = Product::with('category')
                ->whereRaw('(stock_gram - reserved_stock_gram) <= low_stock_threshold_gram')
                ->orderByRaw('(stock_gram - reserved_stock_gram) ASC')
                ->get();

            foreach ($products as $product) {
                $physical = (int) $product->stock_gram;
                $reserved = (int) $product->reserved_stock_gram;
                $available = max(0, $physical - $reserved);
                $threshold = (int) $product->low_stock_threshold_gram;
                $restockNeededGram = max(0, $threshold - $available);

                $status = 'Menipis';
                if ($available === 0) {
                    $status = 'Habis';
                } elseif ($available <= ($threshold / 2)) {
                    $status = 'Kritis';
                }

                fputcsv($file, [
                    $product->sku,
                    $product->name,
                    $product->category?->name ?? 'Tanpa Kategori',
                    $physical,
                    number_format($physical / 1000, 2, '.', ''),
                    $reserved,
                    $available,
                    number_format($available / 1000, 2, '.', ''),
                    $threshold,
                    number_format($threshold / 1000, 2, '.', ''),
                    number_format($restockNeededGram / 1000, 2, '.', ''),
                    $status,
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }
}
