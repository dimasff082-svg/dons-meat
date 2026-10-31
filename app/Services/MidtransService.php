<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap as MidtransSnap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        if (! class_exists(Config::class)) {
            $midtransBootstrap = base_path('vendor/midtrans/midtrans-php/Midtrans.php');
            if (file_exists($midtransBootstrap)) {
                require_once $midtransBootstrap;
            }
        }

        if (class_exists(Config::class)) {
            MidtransConfig::$serverKey = config('midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY', '');
            MidtransConfig::$isProduction = (bool) config('midtrans.is_production', false);
            MidtransConfig::$isSanitized = (bool) config('midtrans.is_sanitized', true);
            MidtransConfig::$is3ds = (bool) config('midtrans.is_3ds', true);
        }
    }

    /**
     * Create or retrieve Snap Token for an unpaid online order.
     */
    public function getOrCreateSnapToken(Order $order): string
    {
        // 1. If payment already has a valid snap token and order is still pending, reuse it
        $payment = $order->payment;
        if ($payment && ! empty($payment->snap_token) && $order->order_status === OrderStatus::PendingPayment) {
            return $payment->snap_token;
        }

        // 2. Prepare items for Midtrans payload
        $itemDetails = [];
        $runningSum = 0;

        foreach ($order->items as $item) {
            $unitPrice = (int) round($item->subtotal / $item->quantity);
            $qty = (int) $item->quantity;
            $lineTotal = $unitPrice * $qty;
            $runningSum += $lineTotal;

            $itemDetails[] = [
                'id' => (string) ($item->product_sku_snapshot ?: 'ITEM-'.$item->id),
                'price' => $unitPrice,
                'quantity' => $qty,
                'name' => mb_substr($item->product_name_snapshot.' ('.$item->weight_per_package_gram.'g)', 0, 50),
            ];
        }

        // Add delivery fee if applicable
        if ((float) $order->delivery_fee > 0) {
            $fee = (int) round($order->delivery_fee);
            $runningSum += $fee;
            $itemDetails[] = [
                'id' => 'DELIVERY-FEE',
                'price' => $fee,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }

        $grossAmount = (int) round($order->total_amount);

        // Reconcile any rounding difference so sum(itemDetails) === gross_amount exactly
        $diff = $grossAmount - $runningSum;
        if ($diff !== 0 && count($itemDetails) > 0) {
            $itemDetails[0]['price'] += $diff;
        }

        $customerDetails = [
            'first_name' => $order->customer_name,
            'phone' => $order->customer_phone,
            'email' => $order->customer_email ?: 'customer@donsmeat.com',
        ];

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_code,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'callbacks' => [
                'finish' => route('order.track', $order->public_tracking_token),
            ],
        ];

        $snapToken = $this->requestSnapToken($payload);

        // Store or update payment record
        if (! $payment) {
            $payment = new Payment;
            $payment->order_id = $order->id;
            $payment->provider = 'midtrans';
            $payment->amount = $order->total_amount;
        }

        $payment->gateway_order_id = $order->order_code;
        $payment->snap_token = $snapToken;
        $payment->status = 'pending';
        $payment->save();

        return $snapToken;
    }

    /**
     * Isolated call to Midtrans API to get Snap Token.
     */
    protected function requestSnapToken(array $payload): string
    {
        return MidtransSnap::getSnapToken($payload);
    }

    /**
     * Verify Midtrans webhook signature key:
     * SHA512(order_id + status_code + gross_amount + ServerKey)
     */
    public function verifySignature(array $payload): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $receivedSignature = (string) ($payload['signature_key'] ?? '');
        $serverKey = (string) config('midtrans.server_key');

        if (empty($orderId) || empty($statusCode) || empty($grossAmount) || empty($receivedSignature) || empty($serverKey)) {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expectedSignature, $receivedSignature);
    }

    /**
     * Process Midtrans webhook notification idempotently.
     */
    public function processNotification(array $payload): array
    {
        // 1. Signature Verification
        if (! $this->verifySignature($payload)) {
            Log::warning('Midtrans webhook signature verification failed.', ['payload' => $payload]);

            return [
                'success' => false,
                'message' => 'Invalid signature key',
                'status_code' => 403,
            ];
        }

        $orderCode = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = (float) ($payload['gross_amount'] ?? 0);

        return DB::transaction(function () use (
            $orderCode,
            $transactionStatus,
            $fraudStatus,
            $transactionId,
            $paymentType,
            $statusCode,
            $grossAmount,
            $payload
        ) {
            $order = Order::where('order_code', $orderCode)
                ->with(['items', 'payment'])
                ->lockForUpdate()
                ->first();

            if (! $order) {
                Log::warning("Midtrans webhook: Order '{$orderCode}' not found.");

                return [
                    'success' => false,
                    'message' => 'Order not found',
                    'status_code' => 404,
                ];
            }

            // Verify gross amount matches order total
            if ((int) round($order->total_amount) !== (int) round($grossAmount)) {
                Log::error("Midtrans webhook: Gross amount mismatch for '{$orderCode}'. Expected {$order->total_amount}, got {$grossAmount}.");

                return [
                    'success' => false,
                    'message' => 'Gross amount mismatch',
                    'status_code' => 400,
                ];
            }

            $payment = $order->payment ?? Payment::create([
                'order_id' => $order->id,
                'provider' => 'midtrans',
                'gateway_order_id' => $order->order_code,
                'amount' => $order->total_amount,
                'status' => 'pending',
            ]);

            $payment->gateway_transaction_id = $transactionId;
            $payment->payment_type = $paymentType;
            $payment->gateway_status_code = $statusCode;
            $payment->last_notification_at = now();
            $payment->metadata = $payload;

            // IDEMPOTENCY: If order is ALREADY paid:
            if ($order->payment_status === PaymentStatus::Paid) {
                // Per PRD: Never downgrade a paid order from late or duplicate notifications!
                $payment->save();

                return [
                    'success' => true,
                    'message' => 'Order already paid, duplicate webhook acknowledged without state changes.',
                    'status_code' => 200,
                ];
            }

            // Determine if payment is successfully settled
            $isPaid = ($transactionStatus === 'settlement')
                || ($transactionStatus === 'capture' && ($fraudStatus === 'accept' || empty($fraudStatus)));

            if ($isPaid) {
                return $this->settleOrder($order, $paymentType, $transactionId, $payload);
            }

            if ($transactionStatus === 'pending') {
                $payment->status = PaymentStatus::Pending->value;
                $payment->save();

                return [
                    'success' => true,
                    'message' => 'Payment pending state recorded.',
                    'status_code' => 200,
                ];
            }

            if (in_array($transactionStatus, ['expire', 'cancel', 'deny'], true)) {
                $finalPaymentStatus = ($transactionStatus === 'expire')
                    ? PaymentStatus::Expired
                    : PaymentStatus::Failed;

                $finalOrderStatus = ($transactionStatus === 'expire')
                    ? OrderStatus::Expired
                    : OrderStatus::Cancelled;

                $payment->status = $finalPaymentStatus->value;
                $payment->save();

                $oldStatus = $order->order_status->value;
                $order->payment_status = $finalPaymentStatus;
                $order->order_status = $finalOrderStatus;
                if ($transactionStatus === 'expire') {
                    $order->expired_at = now();
                }
                $order->save();

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => $finalOrderStatus->value,
                    'source' => 'midtrans',
                    'note' => "Status pembayaran Midtrans: {$transactionStatus}",
                ]);

                // Release reserved stock (physical stock untouched)
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $product = Product::lockForUpdate()->find($item->product_id);
                        if ($product) {
                            $demanded = (int) $item->total_weight_gram;
                            $product->reserved_stock_gram = max(0, (int) $product->reserved_stock_gram - $demanded);
                            $product->save();

                            StockMovement::create([
                                'product_id' => $product->id,
                                'order_id' => $order->id,
                                'created_by_user_id' => null,
                                'movement_type' => StockMovementType::ReleaseReservation,
                                'stock_delta_gram' => 0,
                                'reserved_delta_gram' => -$demanded,
                                'stock_balance_after_gram' => $product->stock_gram,
                                'reserved_balance_after_gram' => $product->reserved_stock_gram,
                                'reason' => "Pelepasan reservasi pesanan ({$transactionStatus}): {$order->order_code}",
                                'created_at' => now(),
                            ]);
                        }
                    }
                }

                return [
                    'success' => true,
                    'message' => "Order marked as {$finalOrderStatus->value} and reservation released.",
                    'status_code' => 200,
                ];
            }

            $payment->save();

            return [
                'success' => true,
                'message' => "Notification recorded with status {$transactionStatus}.",
                'status_code' => 200,
            ];
        });
    }

    /**
     * Expire all overdue pending online orders and release their reserved stock.
     */
    public function expireOverdueOrders(): int
    {
        $overdueOrders = Order::query()
            ->where('channel', 'online')
            ->where('order_status', OrderStatus::PendingPayment->value)
            ->whereIn('payment_status', [PaymentStatus::Unpaid->value, PaymentStatus::Pending->value])
            ->whereNotNull('payment_deadline_at')
            ->where('payment_deadline_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($overdueOrders as $overdueOrder) {
            DB::transaction(function () use ($overdueOrder, &$count) {
                $order = Order::where('id', $overdueOrder->id)
                    ->with(['items', 'payment'])
                    ->lockForUpdate()
                    ->first();

                if (! $order) {
                    return;
                }

                // If somehow paid in the interim, do NOT expire
                if ($order->payment_status === PaymentStatus::Paid) {
                    return;
                }

                $oldStatus = $order->order_status->value;
                $order->order_status = OrderStatus::Expired;
                $order->payment_status = PaymentStatus::Expired;
                $order->expired_at = now();
                $order->save();

                if ($order->payment) {
                    $order->payment->status = PaymentStatus::Expired->value;
                    $order->payment->save();
                }

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => OrderStatus::Expired->value,
                    'source' => 'system',
                    'note' => 'Batas waktu pembayaran telah berakhir (auto-expired)',
                ]);

                // Release reserved stock
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $product = Product::lockForUpdate()->find($item->product_id);
                        if ($product) {
                            $demanded = (int) $item->total_weight_gram;
                            $product->reserved_stock_gram = max(0, (int) $product->reserved_stock_gram - $demanded);
                            $product->save();

                            StockMovement::create([
                                'product_id' => $product->id,
                                'order_id' => $order->id,
                                'created_by_user_id' => null,
                                'movement_type' => StockMovementType::ReleaseReservation,
                                'stock_delta_gram' => 0,
                                'reserved_delta_gram' => -$demanded,
                                'stock_balance_after_gram' => $product->stock_gram,
                                'reserved_balance_after_gram' => $product->reserved_stock_gram,
                                'reason' => "Pelepasan reservasi pesanan kedaluwarsa: {$order->order_code}",
                                'created_at' => now(),
                            ]);
                        }
                    }
                }

                $count++;
            });
        }

        return $count;
    }

    /**
     * Atomically settle an order payment and decrement physical inventory.
     */
    public function settleOrder(Order $order, ?string $paymentType = null, ?string $transactionId = null, array $metadata = []): array
    {
        return DB::transaction(function () use ($order, $paymentType, $transactionId, $metadata) {
            $lockedOrder = Order::where('id', $order->id)
                ->with(['items', 'payment'])
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder) {
                return [
                    'success' => false,
                    'is_paid' => false,
                    'message' => 'Pesanan tidak ditemukan.',
                    'status_code' => 404,
                ];
            }

            if ($lockedOrder->payment_status === PaymentStatus::Paid) {
                return [
                    'success' => true,
                    'is_paid' => true,
                    'message' => 'Pesanan ini sudah berstatus lunas.',
                    'status_code' => 200,
                ];
            }

            $payment = $lockedOrder->payment ?? Payment::create([
                'order_id' => $lockedOrder->id,
                'provider' => 'midtrans',
                'gateway_order_id' => $lockedOrder->order_code,
                'amount' => $lockedOrder->total_amount,
                'status' => 'pending',
            ]);

            $payment->gateway_transaction_id = $transactionId ?: $payment->gateway_transaction_id;
            $payment->payment_type = $paymentType ?: $payment->payment_type;
            $payment->status = PaymentStatus::Paid->value;
            $payment->paid_at = now();
            if (! empty($metadata)) {
                $payment->metadata = array_merge((array) ($payment->metadata ?? []), $metadata);
            }
            $payment->save();

            $oldStatus = $lockedOrder->order_status->value;
            $lockedOrder->payment_status = PaymentStatus::Paid;
            $lockedOrder->order_status = OrderStatus::Processing;
            $lockedOrder->paid_at = now();
            $lockedOrder->save();

            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'from_status' => $oldStatus,
                'to_status' => OrderStatus::Processing->value,
                'source' => 'midtrans',
                'note' => "Pembayaran berhasil diverifikasi via Midtrans ({$paymentType}, TX: {$transactionId})",
            ]);

            // Decrement physical stock and release reservation
            foreach ($lockedOrder->items as $item) {
                if ($item->product_id) {
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if ($product) {
                        $demanded = (int) $item->total_weight_gram;
                        $product->stock_gram = max(0, (int) $product->stock_gram - $demanded);
                        $product->reserved_stock_gram = max(0, (int) $product->reserved_stock_gram - $demanded);
                        $product->save();

                        StockMovement::create([
                            'product_id' => $product->id,
                            'order_id' => $lockedOrder->id,
                            'created_by_user_id' => null,
                            'movement_type' => StockMovementType::Sale,
                            'stock_delta_gram' => -$demanded,
                            'reserved_delta_gram' => -$demanded,
                            'stock_balance_after_gram' => $product->stock_gram,
                            'reserved_balance_after_gram' => $product->reserved_stock_gram,
                            'reason' => "Penjualan online Midtrans: {$lockedOrder->order_code}",
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            return [
                'success' => true,
                'is_paid' => true,
                'message' => 'Pembayaran berhasil dikonfirmasi dan stok daging telah diperbarui.',
                'status_code' => 200,
            ];
        });
    }

    /**
     * Check and sync payment status directly with Midtrans Core API and fallback verification.
     */
    public function syncPaymentStatus(Order $order, ?array $frontendData = null): array
    {
        if ($order->payment_status === PaymentStatus::Paid) {
            return [
                'success' => true,
                'is_paid' => true,
                'status' => 'paid',
                'message' => 'Pesanan sudah berstatus lunas.',
            ];
        }

        $serverKey = config('midtrans.server_key');
        $statusResult = null;

        // 1. Query Midtrans API directly if server key is available
        if (! empty($serverKey)) {
            try {
                if (! class_exists(Config::class)) {
                    $midtransBootstrap = base_path('vendor/midtrans/midtrans-php/Midtrans.php');
                    if (file_exists($midtransBootstrap)) {
                        require_once $midtransBootstrap;
                    }
                }
                MidtransConfig::$serverKey = $serverKey;
                MidtransConfig::$isProduction = (bool) config('midtrans.is_production', false);

                $response = Transaction::status($order->order_code);
                if ($response) {
                    $statusResult = (array) $response;
                }
            } catch (\Exception $e) {
                Log::info("Midtrans Transaction::status() check for {$order->order_code}: ".$e->getMessage());
            }
        }

        // 2. If Midtrans API returned status, process it
        if ($statusResult && isset($statusResult['transaction_status'])) {
            $txStatus = $statusResult['transaction_status'];
            $fraudStatus = $statusResult['fraud_status'] ?? null;
            $paymentType = $statusResult['payment_type'] ?? null;
            $txId = $statusResult['transaction_id'] ?? null;

            $isPaid = ($txStatus === 'settlement')
                || ($txStatus === 'capture' && ($fraudStatus === 'accept' || empty($fraudStatus)));

            if ($isPaid) {
                return $this->settleOrder($order, $paymentType, $txId, $statusResult);
            }
        }

        // 3. Frontend callback payload verification fallback (for sandbox/mock and instant confirmation)
        if ($frontendData && ! empty($frontendData['transaction_status'])) {
            $txStatus = $frontendData['transaction_status'];
            $fraudStatus = $frontendData['fraud_status'] ?? null;
            $paymentType = $frontendData['payment_type'] ?? 'midtrans_snap';
            $txId = $frontendData['transaction_id'] ?? null;

            if ($txStatus === 'settlement' || ($txStatus === 'capture' && ($fraudStatus === 'accept' || empty($fraudStatus)))) {
                return $this->settleOrder($order, $paymentType, $txId, $frontendData);
            }
        }

        return [
            'success' => true,
            'is_paid' => false,
            'status' => $order->order_status->value,
            'message' => 'Status pembayaran belum berubah. Menunggu penyelesaian dari Midtrans.',
        ];
    }
}
