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

        $pendingOrders = Order::where('order_status', OrderStatus::PendingPayment)->count();
        $processingOrders = Order::where('order_status', OrderStatus::Processing)->count();
        $readyOrders = Order::whereIn('order_status', [OrderStatus::ReadyForPickup, OrderStatus::OnDelivery])->count();

        $paidOrdersToday = Order::where('payment_status', PaymentStatus::Paid)
            ->whereDate(DB::raw('COALESCE(paid_at, created_at)'), today())
            ->count();

        $todayRevenue = (float) Order::where('payment_status', PaymentStatus::Paid)
            ->whereDate(DB::raw('COALESCE(paid_at, created_at)'), today())
            ->sum('total_amount');

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
