<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\TestCase;

class MidtransIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected string $serverKey = 'SB-Mid-server-TESTKEY12345';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('midtrans.server_key', $this->serverKey);
        Config::set('midtrans.client_key', 'SB-Mid-client-TESTKEY12345');
        Config::set('midtrans.is_production', false);
    }

    /**
     * Helper to compute a valid Midtrans SHA512 signature.
     */
    protected function generateSignature(string $orderId, string $statusCode, string $grossAmount): string
    {
        return hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);
    }

    public function test_guest_can_request_snap_token_for_unpaid_order(): void
    {
        $order = Order::factory()->create([
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
            'total_amount' => 150000.00,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 1,
            'subtotal' => 150000.00,
            'weight_per_package_gram' => 1000,
        ]);

        // Mock MidtransService so external HTTP call isn't fired in test
        $mockService = Mockery::mock(MidtransService::class)->makePartial();
        $mockService->shouldReceive('getOrCreateSnapToken')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->id === $order->id))
            ->andReturn('dummy-snap-token-123456');

        $this->app->instance(MidtransService::class, $mockService);

        $response = $this->postJson("/pesanan/{$order->public_tracking_token}/snap-token");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'snap_token' => 'dummy-snap-token-123456',
        ]);
    }

    public function test_cannot_request_snap_token_for_already_paid_order(): void
    {
        $order = Order::factory()->create([
            'order_status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $response = $this->postJson("/pesanan/{$order->public_tracking_token}/snap-token");

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Pesanan ini sudah dibayar.',
        ]);
    }

    public function test_cannot_request_snap_token_for_expired_order(): void
    {
        $order = Order::factory()->create([
            'order_status' => OrderStatus::Expired,
            'payment_status' => PaymentStatus::Expired,
        ]);

        $response = $this->postJson("/pesanan/{$order->public_tracking_token}/snap-token");

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Batas waktu pembayaran untuk pesanan ini telah kedaluwarsa.',
        ]);
    }

    public function test_webhook_rejects_invalid_signature_with_403(): void
    {
        $payload = [
            'order_id' => 'DM-INVALID-001',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => 'invalid-fake-signature',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson('/midtrans/notification', $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid signature key',
        ]);
    }

    public function test_webhook_rejects_gross_amount_mismatch_with_400(): void
    {
        $order = Order::factory()->create([
            'order_code' => 'DM-MISMATCH-001',
            'total_amount' => 150000.00,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $tamperedAmount = '100000.00';
        $signature = $this->generateSignature($order->order_code, '200', $tamperedAmount);

        $payload = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => $tamperedAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson('/midtrans/notification', $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Gross amount mismatch',
        ]);
    }

    public function test_webhook_settlement_marks_order_paid_and_decrements_stock_atomically(): void
    {
        $product = Product::factory()->create([
            'stock_gram' => 10000,
            'reserved_stock_gram' => 2000,
        ]);

        $order = Order::factory()->create([
            'order_code' => 'DM-SETTLE-001',
            'total_amount' => 250000.00,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'total_weight_gram' => 2000,
            'subtotal' => 250000.00,
        ]);

        $grossAmount = '250000.00';
        $signature = $this->generateSignature($order->order_code, '200', $grossAmount);

        $payload = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-midtrans-999',
            'payment_type' => 'qris',
        ];

        $response = $this->postJson('/midtrans/notification', $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify order state
        $order->refresh();
        $this->assertEquals(OrderStatus::Processing, $order->order_status);
        $this->assertEquals(PaymentStatus::Paid, $order->payment_status);
        $this->assertNotNull($order->paid_at);

        // Verify stock decremented physically and reserved released
        $product->refresh();
        $this->assertEquals(8000, $product->stock_gram);
        $this->assertEquals(0, $product->reserved_stock_gram);

        // Verify StockMovement logged
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'movement_type' => StockMovementType::Sale->value,
            'stock_delta_gram' => -2000,
            'reserved_delta_gram' => -2000,
        ]);

        // Verify OrderStatusHistory
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => OrderStatus::PendingPayment->value,
            'to_status' => OrderStatus::Processing->value,
            'source' => 'midtrans',
        ]);
    }

    public function test_webhook_is_idempotent_when_called_multiple_times(): void
    {
        $product = Product::factory()->create([
            'stock_gram' => 5000,
            'reserved_stock_gram' => 1000,
        ]);

        $order = Order::factory()->create([
            'order_code' => 'DM-IDEMPOTENT-001',
            'total_amount' => 120000.00,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'total_weight_gram' => 1000,
            'subtotal' => 120000.00,
        ]);

        $grossAmount = '120000.00';
        $signature = $this->generateSignature($order->order_code, '200', $grossAmount);

        $payload = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-midtrans-repeat',
            'payment_type' => 'bank_transfer',
        ];

        // Call 1st time
        $response1 = $this->postJson('/midtrans/notification', $payload);
        $response1->assertStatus(200);

        // Call 2nd time (duplicate delivery from Midtrans)
        $response2 = $this->postJson('/midtrans/notification', $payload);
        $response2->assertStatus(200);

        // Stock MUST only be decremented once!
        $product->refresh();
        $this->assertEquals(4000, $product->stock_gram);
        $this->assertEquals(0, $product->reserved_stock_gram);

        // Only 1 sale movement must exist
        $this->assertEquals(1, StockMovement::where('order_id', $order->id)->where('movement_type', StockMovementType::Sale)->count());
    }

    public function test_webhook_expire_or_cancel_does_not_downgrade_paid_order(): void
    {
        $order = Order::factory()->create([
            'order_code' => 'DM-ALREADY-PAID',
            'total_amount' => 100000.00,
            'order_status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);

        $grossAmount = '100000.00';
        $signature = $this->generateSignature($order->order_code, '200', $grossAmount);

        $payload = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
            'transaction_id' => 'trx-late-expire',
        ];

        $response = $this->postJson('/midtrans/notification', $payload);
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(OrderStatus::Processing, $order->order_status);
        $this->assertEquals(PaymentStatus::Paid, $order->payment_status);
    }

    public function test_webhook_expire_releases_reserved_stock_and_marks_order_expired(): void
    {
        $product = Product::factory()->create([
            'stock_gram' => 5000,
            'reserved_stock_gram' => 1000,
        ]);

        $order = Order::factory()->create([
            'order_code' => 'DM-EXPIRE-001',
            'total_amount' => 100000.00,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'total_weight_gram' => 1000,
            'subtotal' => 100000.00,
        ]);

        $grossAmount = '100000.00';
        $signature = $this->generateSignature($order->order_code, '200', $grossAmount);

        $payload = [
            'order_id' => $order->order_code,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
            'transaction_id' => 'trx-expire-001',
        ];

        $response = $this->postJson('/midtrans/notification', $payload);
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(OrderStatus::Expired, $order->order_status);
        $this->assertEquals(PaymentStatus::Expired, $order->payment_status);
        $this->assertNotNull($order->expired_at);

        // Physical stock remains 5000, reservation returned to 0
        $product->refresh();
        $this->assertEquals(5000, $product->stock_gram);
        $this->assertEquals(0, $product->reserved_stock_gram);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'movement_type' => StockMovementType::ReleaseReservation->value,
            'stock_delta_gram' => 0,
            'reserved_delta_gram' => -1000,
        ]);
    }

    public function test_scheduled_artisan_command_expires_overdue_orders_and_releases_reserved_stock(): void
    {
        $product = Product::factory()->create([
            'stock_gram' => 10000,
            'reserved_stock_gram' => 2500,
        ]);

        // Overdue order (deadline was 10 minutes ago)
        $overdueOrder = Order::factory()->create([
            'order_code' => 'DM-OVERDUE-001',
            'channel' => 'online',
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
            'payment_deadline_at' => now()->subMinutes(10),
        ]);

        OrderItem::factory()->create([
            'order_id' => $overdueOrder->id,
            'product_id' => $product->id,
            'total_weight_gram' => 2500,
        ]);

        // Active order (deadline in 50 minutes)
        $activeOrder = Order::factory()->create([
            'order_code' => 'DM-ACTIVE-001',
            'channel' => 'online',
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
            'payment_deadline_at' => now()->addMinutes(50),
        ]);

        $this->artisan('orders:expire-unpaid')
            ->expectsOutputToContain('Successfully expired 1 overdue order(s)')
            ->assertExitCode(0);

        // Overdue order is expired
        $overdueOrder->refresh();
        $this->assertEquals(OrderStatus::Expired, $overdueOrder->order_status);
        $this->assertEquals(PaymentStatus::Expired, $overdueOrder->payment_status);

        // Active order is still pending
        $activeOrder->refresh();
        $this->assertEquals(OrderStatus::PendingPayment, $activeOrder->order_status);
        $this->assertEquals(PaymentStatus::Unpaid, $activeOrder->payment_status);

        // Reserved stock is released
        $product->refresh();
        $this->assertEquals(10000, $product->stock_gram);
        $this->assertEquals(0, $product->reserved_stock_gram);
    }
}
