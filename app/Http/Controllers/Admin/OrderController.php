<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a paginated list of orders with filters and summary counters.
     */
    public function index(Request $request): View
    {
        $query = Order::query()->with(['items', 'deliveryZone', 'createdByUser'])->latest('id');

        // Filter by Order Status
        if ($request->filled('status')) {
            $statusEnum = OrderStatus::tryFrom($request->string('status')->value());
            if ($statusEnum) {
                $query->where('order_status', $statusEnum);
            }
        }

        // Filter by Payment Status
        if ($request->filled('payment_status')) {
            $paymentEnum = PaymentStatus::tryFrom($request->string('payment_status')->value());
            if ($paymentEnum) {
                $query->where('payment_status', $paymentEnum);
            }
        }

        // Filter by Channel
        if ($request->filled('channel')) {
            $channelEnum = OrderChannel::tryFrom($request->string('channel')->value());
            if ($channelEnum) {
                $query->where('channel', $channelEnum);
            }
        }

        // Filter by Fulfillment Method
        if ($request->filled('fulfillment_method')) {
            $fulfillmentEnum = FulfillmentMethod::tryFrom($request->string('fulfillment_method')->value());
            if ($fulfillmentEnum) {
                $query->where('fulfillment_method', $fulfillmentEnum);
            }
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
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

        $orders = $query->paginate(15)->withQueryString();

        // Summary counts for quick tabs / badges (optimized to a single grouped query)
        $statusCounts = Order::query()
            ->select('order_status', DB::raw('count(*) as aggregate'))
            ->groupBy('order_status')
            ->pluck('aggregate', 'order_status')
            ->all();

        $counters = [
            'all' => array_sum($statusCounts),
            'pending_payment' => $statusCounts[OrderStatus::PendingPayment->value] ?? 0,
            'processing' => $statusCounts[OrderStatus::Processing->value] ?? 0,
            'ready_for_pickup' => $statusCounts[OrderStatus::ReadyForPickup->value] ?? 0,
            'on_delivery' => $statusCounts[OrderStatus::OnDelivery->value] ?? 0,
            'completed' => $statusCounts[OrderStatus::Completed->value] ?? 0,
            'cancelled' => $statusCounts[OrderStatus::Cancelled->value] ?? 0,
            'expired' => $statusCounts[OrderStatus::Expired->value] ?? 0,
        ];

        return view('admin.orders.index', compact('orders', 'counters'));
    }

    /**
     * Display detailed order view with item snapshots, financial breakdown, and status timeline.
     */
    public function show(Order $order): View
    {
        $order->load([
            'items.product',
            'payment',
            'statusHistories.actorUser',
            'createdByUser',
            'deliveryZone',
            'stockMovements.product',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update order fulfillment status (processing -> ready_for_pickup/on_delivery -> completed).
     */
    public function updateFulfillment(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_status' => [
                'required',
                Rule::in([
                    OrderStatus::ReadyForPickup->value,
                    OrderStatus::OnDelivery->value,
                    OrderStatus::Completed->value,
                ]),
            ],
            'note' => 'nullable|string|max:500',
        ]);

        $targetStatus = OrderStatus::from($validated['order_status']);

        if (! $order->canTransitionTo($targetStatus)) {
            return back()->withErrors([
                'order_status' => "Perubahan status dari '{$order->order_status->label()}' ke '{$targetStatus->label()}' tidak diizinkan.",
            ]);
        }

        DB::transaction(function () use ($order, $targetStatus, $validated) {
            $oldStatus = $order->order_status;
            $order->order_status = $targetStatus;

            if ($targetStatus === OrderStatus::Completed) {
                $order->completed_at = now();

                // If order was unpaid (e.g. COD / Cash on Delivery), complete payment and record stock sale
                if ($order->payment_status !== PaymentStatus::Paid) {
                    $order->payment_status = PaymentStatus::Paid;
                    $order->paid_at = now();

                    if ($order->payment) {
                        $order->payment->status = PaymentStatus::Paid->value;
                        $order->payment->paid_at = now();
                        $order->payment->save();
                    }

                    // Physically decrement stock_gram and release reserved_stock_gram
                    foreach ($order->items as $item) {
                        if ($item->product_id) {
                            $product = Product::lockForUpdate()->find($item->product_id);
                            if ($product) {
                                $demanded = (int) $item->total_weight_gram;
                                $product->stock_gram = max(0, (int) $product->stock_gram - $demanded);
                                $product->reserved_stock_gram = max(0, (int) $product->reserved_stock_gram - $demanded);
                                $product->save();

                                StockMovement::create([
                                    'product_id' => $product->id,
                                    'order_id' => $order->id,
                                    'created_by_user_id' => auth()->id(),
                                    'movement_type' => StockMovementType::Sale,
                                    'stock_delta_gram' => -$demanded,
                                    'reserved_delta_gram' => -$demanded,
                                    'stock_balance_after_gram' => $product->stock_gram,
                                    'reserved_balance_after_gram' => $product->reserved_stock_gram,
                                    'reason' => "Penjualan pesanan selesai ({$order->payment_method}): {$order->order_code}",
                                    'created_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }

            $order->save();

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus->value,
                'to_status' => $targetStatus->value,
                'actor_user_id' => auth()->id(),
                'source' => 'admin',
                'note' => ! empty($validated['note'])
                    ? trim($validated['note'])
                    : "Status pesanan diubah menjadi {$targetStatus->label()} oleh ".auth()->user()->name,
            ]);
        });

        return back()->with('status', "Status pesanan {$order->order_code} berhasil diperbarui menjadi '{$targetStatus->label()}'.");
    }

    /**
     * Cancel an unpaid order and safely release any reserved stock.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        if (! $order->canBeCancelled()) {
            return back()->withErrors([
                'order' => 'Pesanan yang sudah lunas, sudah kedaluwarsa, atau sudah dibatalkan tidak dapat dibatalkan melalui sistem.',
            ]);
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($order, $validated) {
            $lockedOrder = Order::where('id', $order->id)
                ->with('items')
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedOrder->canBeCancelled()) {
                throw new \Exception('Pesanan tidak memenuhi syarat untuk dibatalkan.');
            }

            $oldStatus = $lockedOrder->order_status;
            $lockedOrder->order_status = OrderStatus::Cancelled;
            $lockedOrder->payment_status = PaymentStatus::Cancelled;
            $lockedOrder->save();

            if ($lockedOrder->payment) {
                $lockedOrder->payment->status = PaymentStatus::Cancelled->value;
                $lockedOrder->payment->save();
            }

            $reason = ! empty($validated['cancellation_reason'])
                ? trim($validated['cancellation_reason'])
                : 'Dibatalkan secara manual oleh admin';

            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'from_status' => $oldStatus->value,
                'to_status' => OrderStatus::Cancelled->value,
                'actor_user_id' => auth()->id(),
                'source' => 'admin',
                'note' => 'Pembatalan oleh '.auth()->user()->name.". Alasan: {$reason}",
            ]);

            // Safely release any reserved stock
            foreach ($lockedOrder->items as $item) {
                if ($item->product_id) {
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if ($product) {
                        $demanded = (int) $item->total_weight_gram;
                        $product->reserved_stock_gram = max(0, (int) $product->reserved_stock_gram - $demanded);
                        $product->save();

                        StockMovement::create([
                            'product_id' => $product->id,
                            'order_id' => $lockedOrder->id,
                            'created_by_user_id' => auth()->id(),
                            'movement_type' => StockMovementType::ReleaseReservation,
                            'stock_delta_gram' => 0,
                            'reserved_delta_gram' => -$demanded,
                            'stock_balance_after_gram' => $product->stock_gram,
                            'reserved_balance_after_gram' => $product->reserved_stock_gram,
                            'reason' => "Pelepasan reservasi karena pembatalan pesanan: {$lockedOrder->order_code}",
                            'created_at' => now(),
                        ]);
                    }
                }
            }
        });

        return back()->with('status', "Pesanan {$order->order_code} berhasil dibatalkan dan reservasi stok telah dilepas.");
    }
}
