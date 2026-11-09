<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductWeightOption;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DirectSaleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with(['items', 'createdByUser', 'payments'])
            ->where('channel', OrderChannel::InStore)
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->string('date'));
        }

        $orders = $query->paginate(15)->withQueryString();

        $todaySummary = Order::where('channel', OrderChannel::InStore)
            ->where('payment_status', PaymentStatus::Paid)
            ->whereDate('paid_at', today())
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(total_amount), 0) as total_sum')
            ->first();

        $todaySalesCount = (int) ($todaySummary->total_count ?? 0);
        $todayRevenue = (float) ($todaySummary->total_sum ?? 0);

        return view('admin.direct_sales.index', compact('orders', 'todaySalesCount', 'todayRevenue'));
    }

    public function create(): View
    {
        $products = Product::with(['activeWeightOptions', 'category'])
            ->where('is_active', true)
            ->whereRaw('(stock_gram - reserved_stock_gram) > 0')
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        return view('admin.direct_sales.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->has('amount_paid')) {
            $rawAmount = (string) $request->input('amount_paid');
            // Remove thousand separator dots or spaces, allow decimal point if present
            $cleaned = preg_replace('/[^0-9]/', '', $rawAmount);
            $request->merge([
                'amount_paid' => $cleaned === '' ? 0 : (float) $cleaned,
            ]);
        }

        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_note' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'in:cash,manual_transfer,qris'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.weight_option_id' => ['required', 'integer', 'exists:product_weight_options,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $order = DB::transaction(function () use ($validated) {
            $itemInputs = $validated['items'];

            // 1. Group requested weight by product_id
            $productWeightDemands = [];
            $preparedLines = [];

            foreach ($itemInputs as $idx => $input) {
                $productId = (int) $input['product_id'];
                $weightOptionId = (int) $input['weight_option_id'];
                $qty = (int) $input['quantity'];

                $weightOption = ProductWeightOption::with('product')->find($weightOptionId);

                if (! $weightOption || $weightOption->product_id !== $productId || ! $weightOption->is_active) {
                    throw ValidationException::withMessages([
                        "items.{$idx}.weight_option_id" => 'Pilihan berat yang dipilih tidak valid atau sudah tidak aktif.',
                    ]);
                }

                $product = $weightOption->product;
                if (! $product->is_active) {
                    throw ValidationException::withMessages([
                        "items.{$idx}.product_id" => "Produk {$product->name} saat ini sedang tidak aktif.",
                    ]);
                }

                $packageWeightGram = (int) $weightOption->weight_gram;
                $lineTotalWeightGram = $packageWeightGram * $qty;
                $pricePerKg = (float) $product->price_per_kg;
                $lineSubtotal = round(($pricePerKg * $packageWeightGram / 1000) * $qty, 2);

                if (! isset($productWeightDemands[$productId])) {
                    $productWeightDemands[$productId] = 0;
                }
                $productWeightDemands[$productId] += $lineTotalWeightGram;

                $preparedLines[] = [
                    'product' => $product,
                    'weight_option' => $weightOption,
                    'quantity' => $qty,
                    'package_weight_gram' => $packageWeightGram,
                    'line_total_weight_gram' => $lineTotalWeightGram,
                    'price_per_kg' => $pricePerKg,
                    'line_subtotal' => $lineSubtotal,
                ];
            }

            // 2. Lock each product and verify available stock (physical - reserved)
            $lockedProducts = [];
            foreach ($productWeightDemands as $productId => $demandedGram) {
                $lockedProduct = Product::where('id', $productId)->lockForUpdate()->first();
                $availableGram = max(0, $lockedProduct->stock_gram - $lockedProduct->reserved_stock_gram);

                if ($availableGram < $demandedGram) {
                    throw ValidationException::withMessages([
                        'items' => "Stok untuk produk '{$lockedProduct->name}' tidak mencukupi. Tersedia: ".number_format($availableGram).' g, diminta: '.number_format($demandedGram).' g.',
                    ]);
                }

                $lockedProducts[$productId] = $lockedProduct;
            }

            // 3. Calculate subtotal & validate amount paid
            $subtotal = 0;
            foreach ($preparedLines as $line) {
                $subtotal += $line['line_subtotal'];
            }

            $amountPaid = (float) $validated['amount_paid'];
            if ($validated['payment_method'] === 'cash' && $amountPaid < $subtotal) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Uang tunai yang dibayarkan (Rp '.number_format($amountPaid, 0, ',', '.').') kurang dari total transaksi (Rp '.number_format($subtotal, 0, ',', '.').').',
                ]);
            }

            $change = max(0, $amountPaid - $subtotal);

            // 4. Create Order
            do {
                $orderCode = 'DM-POS-'.date('Ymd').'-'.strtoupper(Str::random(4));
            } while (Order::where('order_code', $orderCode)->exists());

            $customerName = trim($validated['customer_name'] ?? '');
            if ($customerName === '') {
                $customerName = 'Pelanggan Langsung (Walk-in)';
            }

            $order = Order::create([
                'order_code' => $orderCode,
                'public_tracking_token' => Str::random(32),
                'channel' => OrderChannel::InStore,
                'created_by_user_id' => auth()->id(),
                'customer_name' => $customerName,
                'customer_phone' => $validated['customer_phone'] ?? '-',
                'customer_email' => null,
                'fulfillment_method' => FulfillmentMethod::Pickup,
                'delivery_zone_id' => null,
                'delivery_address' => null,
                'subtotal' => $subtotal,
                'delivery_fee' => 0,
                'total_amount' => $subtotal,
                'order_status' => OrderStatus::Completed,
                'payment_status' => PaymentStatus::Paid,
                'customer_note' => $validated['customer_note'] ?? null,
                'internal_note' => 'Penjualan langsung toko diproses oleh '.auth()->user()->name,
                'paid_at' => now(),
                'completed_at' => now(),
            ]);

            // 5. Create Order Items
            foreach ($preparedLines as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'product_name_snapshot' => $line['product']->name,
                    'product_sku_snapshot' => $line['product']->sku,
                    'weight_per_package_gram' => $line['package_weight_gram'],
                    'quantity' => $line['quantity'],
                    'total_weight_gram' => $line['line_total_weight_gram'],
                    'price_per_kg_snapshot' => $line['price_per_kg'],
                    'subtotal' => $line['line_subtotal'],
                ]);
            }

            // 6. Record Payment
            Payment::create([
                'order_id' => $order->id,
                'provider' => 'manual',
                'gateway_order_id' => null,
                'gateway_transaction_id' => null,
                'snap_token' => null,
                'amount' => $subtotal,
                'currency' => 'IDR',
                'payment_type' => $validated['payment_method'],
                'status' => 'settlement',
                'paid_at' => now(),
                'metadata' => [
                    'amount_paid' => $amountPaid,
                    'change' => $change,
                    'cashier_user_id' => auth()->id(),
                    'cashier_name' => auth()->user()->name,
                ],
            ]);

            // 7. Record Order Status History
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => OrderStatus::Completed->value,
                'actor_user_id' => auth()->id(),
                'source' => 'admin',
                'note' => 'Transaksi penjualan langsung toko selesai dan dibayar lunas.',
                'created_at' => now(),
            ]);

            // 8. Decrement physical stock directly and log StockMovement
            foreach ($productWeightDemands as $productId => $demandedGram) {
                $lockedProduct = $lockedProducts[$productId];
                $lockedProduct->decrement('stock_gram', $demandedGram);
                $lockedProduct->refresh();

                StockMovement::create([
                    'product_id' => $lockedProduct->id,
                    'order_id' => $order->id,
                    'created_by_user_id' => auth()->id(),
                    'movement_type' => StockMovementType::Sale,
                    'stock_delta_gram' => -$demandedGram,
                    'reserved_delta_gram' => 0,
                    'stock_balance_after_gram' => $lockedProduct->stock_gram,
                    'reserved_balance_after_gram' => $lockedProduct->reserved_stock_gram,
                    'reason' => "Penjualan langsung toko: {$order->order_code}",
                    'created_at' => now(),
                ]);
            }

            return $order;
        });

        return redirect()->route('admin.direct-sales.show', $order)
            ->with('status', "Transaksi {$order->order_code} berhasil diproses.");
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'createdByUser', 'payments', 'statusHistories.actorUser']);
        $storeSetting = StoreSetting::current();

        return view('admin.direct_sales.show', compact('order', 'storeSetting'));
    }
}
