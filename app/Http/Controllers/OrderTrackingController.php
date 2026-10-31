<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function show(string $token): View
    {
        $order = Order::where('public_tracking_token', $token)
            ->with(['items.product', 'deliveryZone', 'payments', 'statusHistories'])
            ->firstOrFail();

        $storeSetting = StoreSetting::current();

        return view('public.order.track', compact('order', 'storeSetting'));
    }
}
