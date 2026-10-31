<?php

namespace App\Providers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\CartService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.public', 'public.*'], function ($view) {
            $view->with('cartCount', app(CartService::class)->getCount());
            $view->with('storeSetting', StoreSetting::current());
        });

        View::composer('layouts.admin', function ($view) {
            if (auth()->check()) {
                $view->with([
                    'adminProcessingOrdersCount' => once(fn () => Order::where('order_status', OrderStatus::Processing)->count()),
                    'adminPendingPaymentCount' => once(fn () => Order::where('order_status', OrderStatus::PendingPayment)->count()),
                    'adminLowStockCount' => once(fn () => Product::whereRaw('(stock_gram - reserved_stock_gram) <= low_stock_threshold_gram')->count()),
                ]);
            }
        });
    }
}
