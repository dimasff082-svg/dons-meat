<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user?->isCashier()) {
            return redirect()->route('admin.direct-sales.create');
        }

        if ($user?->isOwner()) {
            return redirect()->route('admin.reports.sales');
        }

        $setting = StoreSetting::current();

        $totalProducts = Product::count();
        $lowStockProducts = Product::whereRaw('(stock_gram - reserved_stock_gram) <= low_stock_threshold_gram')->count();
        $lowStockList = Product::whereRaw('(stock_gram - reserved_stock_gram) <= low_stock_threshold_gram')
            ->orderByRaw('(stock_gram - reserved_stock_gram) ASC')
            ->take(5)
            ->get();

        // Single grouped query for order pipeline counts
        $orderCounts = Order::whereIn('order_status', [
            OrderStatus::PendingPayment,
            OrderStatus::Processing,
            OrderStatus::ReadyForPickup,
            OrderStatus::OnDelivery,
        ])
            ->select('order_status', DB::raw('count(*) as count'))
            ->groupBy('order_status')
            ->pluck('count', 'order_status')
            ->all();

        $pendingOrders = $orderCounts[OrderStatus::PendingPayment->value] ?? 0;
        $processingOrders = $orderCounts[OrderStatus::Processing->value] ?? 0;
        $readyOrders = ($orderCounts[OrderStatus::ReadyForPickup->value] ?? 0)
            + ($orderCounts[OrderStatus::OnDelivery->value] ?? 0);

        // Single query for today's sales count and revenue
        $todaySummary = Order::where('payment_status', PaymentStatus::Paid)
            ->whereDate(DB::raw('COALESCE(paid_at, created_at)'), today())
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(total_amount), 0) as total_sum')
            ->first();

        $paidOrdersToday = (int) ($todaySummary->total_count ?? 0);
        $todayRevenue = (float) ($todaySummary->total_sum ?? 0);

        $monthRevenue = (float) Order::where('payment_status', PaymentStatus::Paid)
            ->whereBetween(DB::raw('DATE(COALESCE(paid_at, created_at))'), [
                now()->startOfMonth()->toDateString(),
                now()->toDateString(),
            ])
            ->sum('total_amount');

        $recentOrders = Order::with(['items', 'deliveryZone'])
            ->latest('id')
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'setting',
            'totalProducts',
            'lowStockProducts',
            'lowStockList',
            'pendingOrders',
            'processingOrders',
            'readyOrders',
            'paidOrdersToday',
            'todayRevenue',
            'monthRevenue',
            'recentOrders'
        ));
    }
}
