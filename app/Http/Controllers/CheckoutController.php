<?php

namespace App\Http\Controllers;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductWeightOption;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(): View|RedirectResponse
    {
        $cart = $this->cartService->getDetailedItems();

        if ($cart['items']->isEmpty()) {
            return redirect()->route('catalog.index')
                ->with('error', 'Keranjang belanja Anda masih kosong. Silakan pilih daging terlebih dahulu.');
        }

        $deliveryZones = DeliveryZone::active()->orderBy('fee')->get();
        $storeSetting = StoreSetting::current();

        return view('public.checkout.index', compact('cart', 'deliveryZones', 'storeSetting'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:100'],
            'fulfillment_method' => ['required', 'string', 'in:pickup,delivery'],
            'delivery_zone_id' => [
                'required_if:fulfillment_method,delivery',
                'nullable',
                'integer',
                'exists:delivery_zones,id',
            ],
            'delivery_address' => [
                'required_if:fulfillment_method,delivery',
                'nullable',
                'string',
                'max:500',
            ],
            'payment_method' => ['nullable', 'string', 'in:midtrans,cod'],
            'customer_note' => ['nullable', 'string', 'max:255'],
        ]);

        $rawItems = $this->cartService->getRawItems();
        if (empty($rawItems)) {
            return redirect()->route('catalog.index')
                ->with('error', 'Keranjang belanja kosong.');
        }

        $order = DB::transaction(function () use ($validated, $rawItems) {
            // 1. Group demands and prepare lines
            $productDemands = [];
            $preparedLines = [];

            foreach ($rawItems as $weightOptionId => $raw) {
                $qty = (int) ($raw['quantity'] ?? 1);
                $weightOption = ProductWeightOption::with('product')->find($weightOptionId);

                if (! $weightOption || ! $weightOption->is_active || ! $weightOption->product || ! $weightOption->product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Ada pilihan berat atau produk di keranjang yang sudah tidak aktif. Silakan periksa kembali keranjang Anda.',
                    ]);
                }

                $product = $weightOption->product;
                $pkgGram = (int) $weightOption->weight_gram;
                $lineTotalWeightGram = $pkgGram * $qty;
                $pricePerKg = (float) $product->price_per_kg;
                $lineSubtotal = round(($pricePerKg * $pkgGram / 1000) * $qty, 2);

                $productDemands[$product->id] = ($productDemands[$product->id] ?? 0) + $lineTotalWeightGram;

                $preparedLines[] = [
                    'product' => $product,
                    'weight_option' => $weightOption,
                    'quantity' => $qty,
                    'package_weight_gram' => $pkgGram,
                    'line_total_weight_gram' => $lineTotalWeightGram,
                    'price_per_kg' => $pricePerKg,
                    'line_subtotal' => $lineSubtotal,
                ];
            }

            // 2. Lock each product and verify stock availability (physical - reserved >= demanded)
            $lockedProducts = [];
            foreach ($productDemands as $productId => $demandedGram) {
                $lockedProduct = Product::where('id', $productId)->lockForUpdate()->first();
                $availableStock = max(0, $lockedProduct->stock_gram - $lockedProduct->reserved_stock_gram);

                if ($availableStock < $demandedGram) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok daging '{$lockedProduct->name}' tidak mencukupi untuk pesanan Anda. Tersedia: ".number_format($availableStock).' g, diminta: '.number_format($demandedGram).' g.',
                    ]);
                }

                $lockedProducts[$productId] = $lockedProduct;
            }

            // 3. Calculate subtotal & delivery fee
            $subtotal = 0;
            foreach ($preparedLines as $line) {
                $subtotal += $line['line_subtotal'];
            }

            $fulfillmentMethod = FulfillmentMethod::from($validated['fulfillment_method']);
            $deliveryFee = 0.0;
            $deliveryZoneId = null;
            $deliveryAddress = null;

            if ($fulfillmentMethod === FulfillmentMethod::Delivery) {
                $zone = DeliveryZone::where('id', $validated['delivery_zone_id'])->where('is_active', true)->first();
                if (! $zone) {
                    throw ValidationException::withMessages([
                        'delivery_zone_id' => 'Zona pengiriman yang dipilih tidak aktif atau tidak ditemukan.',
                    ]);
                }

                if ($zone->minimum_order_amount && $subtotal < (float) $zone->minimum_order_amount) {
                    throw ValidationException::withMessages([
                        'delivery_zone_id' => "Minimum belanja untuk zona '{$zone->name}' adalah Rp ".number_format($zone->minimum_order_amount, 0, ',', '.').'.',
                    ]);
                }

                $deliveryFee = (float) $zone->fee;
                $deliveryZoneId = $zone->id;
                $deliveryAddress = trim($validated['delivery_address']);
            }

            $totalAmount = $subtotal + $deliveryFee;

            $setting = StoreSetting::current();
            $expiryMinutes = max(15, (int) $setting->payment_expiry_minutes);
            $paymentMethod = $validated['payment_method'] ?? 'midtrans';
            $isCod = $paymentMethod === 'cod';
            $paymentDeadlineAt = $isCod ? null : now()->addMinutes($expiryMinutes);

            // 4. Generate unique order code & high entropy tracking token
            do {
                $orderCode = 'DM-'.date('Ymd').'-'.strtoupper(Str::random(5));
            } while (Order::where('order_code', $orderCode)->exists());

            $publicTrackingToken = Str::random(40);

            // 5. Create Order
            $order = Order::create([
                'order_code' => $orderCode,
                'public_tracking_token' => $publicTrackingToken,
                'channel' => OrderChannel::Online,
                'created_by_user_id' => null,
                'customer_name' => trim($validated['customer_name']),
                'customer_phone' => trim($validated['customer_phone']),
                'customer_email' => ! empty($validated['customer_email']) ? trim($validated['customer_email']) : null,
                'fulfillment_method' => $fulfillmentMethod,
                'payment_method' => $paymentMethod,
                'delivery_zone_id' => $deliveryZoneId,
                'delivery_address' => $deliveryAddress,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $totalAmount,
                'order_status' => $isCod ? OrderStatus::Processing : OrderStatus::PendingPayment,
                'payment_status' => PaymentStatus::Unpaid,
                'customer_note' => ! empty($validated['customer_note']) ? trim($validated['customer_note']) : null,
                'internal_note' => null,
                'payment_deadline_at' => $paymentDeadlineAt,
            ]);

            // 6. Create Order Items Snapshot
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

            // 7. Reserve Stock (Increment reserved_stock_gram, physical stock_gram stays unchanged!)
            foreach ($productDemands as $productId => $demandedGram) {
                $lockedProduct = $lockedProducts[$productId];
                $lockedProduct->increment('reserved_stock_gram', $demandedGram);
                $lockedProduct->refresh();

                StockMovement::create([
                    'product_id' => $lockedProduct->id,
                    'order_id' => $order->id,
                    'created_by_user_id' => null,
                    'movement_type' => StockMovementType::Reserve,
                    'stock_delta_gram' => 0,
                    'reserved_delta_gram' => $demandedGram,
                    'stock_balance_after_gram' => $lockedProduct->stock_gram,
                    'reserved_balance_after_gram' => $lockedProduct->reserved_stock_gram,
                    'reason' => "Reservasi pesanan online: {$order->order_code}",
                    'created_at' => now(),
                ]);
            }

            // 8. Create Initial Pending Payment Record
            Payment::create([
                'order_id' => $order->id,
                'provider' => $isCod ? 'cod' : 'midtrans',
                'gateway_order_id' => null,
                'gateway_transaction_id' => null,
                'snap_token' => null,
                'amount' => $totalAmount,
                'currency' => 'IDR',
                'payment_type' => $isCod ? 'cash_on_delivery' : null,
                'status' => 'pending',
                'expires_at' => $paymentDeadlineAt,
            ]);

            // 9. Create Order Status History
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => $isCod ? OrderStatus::Processing->value : OrderStatus::PendingPayment->value,
                'actor_user_id' => null,
                'source' => 'customer',
                'note' => $isCod
                    ? 'Pesanan COD dibuat oleh pelanggan, stok dicadangkan, menunggu pemrosesan & pembayaran tunai saat diterima.'
                    : 'Pesanan online dibuat oleh pelanggan, stok dicadangkan, menunggu pembayaran.',
                'created_at' => now(),
            ]);

            return $order;
        });

        // 10. Clear Cart Session
        $this->cartService->clear();

        $flashMessage = $order->isCod()
            ? "Pesanan COD {$order->order_code} berhasil dibuat! Daging segera kami siapkan."
            : "Pesanan {$order->order_code} berhasil dibuat! Silakan lakukan pembayaran sebelum tenggat waktu.";

        return redirect()->route('order.track', ['token' => $order->public_tracking_token])
            ->with('status', $flashMessage);
    }
}
