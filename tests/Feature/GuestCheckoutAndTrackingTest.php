<?php

namespace Tests\Feature;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductWeightOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCheckoutAndTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_redirects_if_cart_is_empty(): void
    {
        $response = $this->get('/checkout');

        $response->assertRedirect('/katalog');
        $response->assertSessionHas('error');
    }

    public function test_guest_can_view_checkout_page_when_cart_has_items(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        $weightOption = ProductWeightOption::factory()->create(['product_id' => $product->id, 'weight_gram' => 500, 'is_active' => true]);
        $zone = DeliveryZone::factory()->create(['name' => 'Makassar Kota', 'is_active' => true]);

        // Add to cart
        $this->post('/keranjang', [
            'product_id' => $product->id,
            'weight_option_id' => $weightOption->id,
            'quantity' => 1,
        ]);

        $response = $this->get('/checkout');

        $response->assertStatus(200);
        $response->assertSee('Checkout Pesanan Daging');
        $response->assertSee('Makassar Kota');
        $response->assertSee($product->name);
    }

    public function test_guest_can_checkout_with_pickup_method_and_reserves_stock(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Tenderloin Segar',
            'sku' => 'DM-TND-001',
            'price_per_kg' => 160000.00,
            'stock_gram' => 20000,
            'reserved_stock_gram' => 1000, // already 1,000 g reserved
            'is_active' => true,
        ]);

        // 500 g -> Price: (160,000 * 500 / 1000) = Rp 80,000
        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 500,
            'label' => '500 g',
            'is_active' => true,
        ]);

        // Add 2 packages to cart (total demanded: 1,000 g; subtotal: Rp 160,000)
        $this->post('/keranjang', [
            'product_id' => $product->id,
            'weight_option_id' => $weightOption->id,
            'quantity' => 2,
        ]);

        $postData = [
            'customer_name' => 'Andi Muhammad',
            'customer_phone' => '081122334455',
            'customer_email' => 'andi@example.com',
            'fulfillment_method' => 'pickup',
            'customer_note' => 'Ambil siang sekitar jam 14.00',
        ];

        $response = $this->post('/checkout', $postData);

        $order = Order::where('customer_name', 'Andi Muhammad')->first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('order.track', ['token' => $order->public_tracking_token]));

        // 1. Verify Order attributes
        $this->assertEquals(OrderChannel::Online, $order->channel);
        $this->assertEquals(FulfillmentMethod::Pickup, $order->fulfillment_method);
        $this->assertEquals(OrderStatus::PendingPayment, $order->order_status);
        $this->assertEquals(PaymentStatus::Unpaid, $order->payment_status);
        $this->assertEquals(160000.00, (float) $order->subtotal);
        $this->assertEquals(0.00, (float) $order->delivery_fee);
        $this->assertEquals(160000.00, (float) $order->total_amount);
        $this->assertNull($order->delivery_zone_id);
        $this->assertNull($order->delivery_address);
        $this->assertNotNull($order->payment_deadline_at);
        $this->assertNull($order->created_by_user_id); // Guest checkout

        // 2. Verify Order Item Snapshots
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name_snapshot' => 'Tenderloin Segar',
            'product_sku_snapshot' => 'DM-TND-001',
            'weight_per_package_gram' => 500,
            'quantity' => 2,
            'total_weight_gram' => 1000,
            'price_per_kg_snapshot' => 160000.00,
            'subtotal' => 160000.00,
        ]);

        // 3. Verify Stock Reservation: physical stock unchanged, reserved stock increased!
        $product->refresh();
        $this->assertEquals(20000, $product->stock_gram); // Untouched!
        $this->assertEquals(2000, $product->reserved_stock_gram); // 1,000 + 1,000 = 2,000 g!

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'movement_type' => StockMovementType::Reserve->value,
            'stock_delta_gram' => 0,
            'reserved_delta_gram' => 1000,
            'stock_balance_after_gram' => 20000,
            'reserved_balance_after_gram' => 2000,
        ]);

        // 4. Verify Cart was cleared
        $this->assertEmpty(session()->get('dons_meat_cart', []));
    }

    public function test_guest_can_checkout_with_delivery_and_accurate_delivery_fee(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price_per_kg' => 100000.00,
            'stock_gram' => 15000,
            'reserved_stock_gram' => 0,
            'is_active' => true,
        ]);
        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 1000, // 1 kg -> Rp 100,000
            'is_active' => true,
        ]);

        $zone = DeliveryZone::factory()->create([
            'name' => 'Zona Panakkukang',
            'fee' => 18000.00,
            'is_active' => true,
        ]);

        $this->post('/keranjang', [
            'product_id' => $product->id,
            'weight_option_id' => $weightOption->id,
            'quantity' => 1,
        ]);

        $response = $this->post('/checkout', [
            'customer_name' => 'Siti Nurhaliza',
            'customer_phone' => '082199887766',
            'fulfillment_method' => 'delivery',
            'delivery_zone_id' => $zone->id,
            'delivery_address' => 'Jl. Boulevard No. 45, Panakkukang',
        ]);

        $order = Order::where('customer_name', 'Siti Nurhaliza')->first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('order.track', ['token' => $order->public_tracking_token]));

        $this->assertEquals(FulfillmentMethod::Delivery, $order->fulfillment_method);
        $this->assertEquals($zone->id, $order->delivery_zone_id);
        $this->assertEquals('Jl. Boulevard No. 45, Panakkukang', $order->delivery_address);
        $this->assertEquals(100000.00, (float) $order->subtotal);
        $this->assertEquals(18000.00, (float) $order->delivery_fee);
        $this->assertEquals(118000.00, (float) $order->total_amount); // 100k + 18k = 118k
    }

    public function test_checkout_fails_if_delivery_zone_is_inactive(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock_gram' => 10000, 'is_active' => true]);
        $weightOption = ProductWeightOption::factory()->create(['product_id' => $product->id, 'weight_gram' => 500, 'is_active' => true]);

        $inactiveZone = DeliveryZone::factory()->create([
            'name' => 'Zona Nonaktif',
            'fee' => 20000.00,
            'is_active' => false, // Inactive zone!
        ]);

        $this->post('/keranjang', [
            'product_id' => $product->id,
            'weight_option_id' => $weightOption->id,
            'quantity' => 1,
        ]);

        $response = $this->post('/checkout', [
            'customer_name' => 'Hasanuddin',
            'customer_phone' => '081234567890',
            'fulfillment_method' => 'delivery',
            'delivery_zone_id' => $inactiveZone->id,
            'delivery_address' => 'Jl. Perintis Kemerdekaan',
        ]);

        $response->assertSessionHasErrors('delivery_zone_id');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_fails_if_demanded_weight_exceeds_available_stock(): void
    {
        $category = Category::factory()->create();
        // Product has only 400 g available (physical 1,400 g - reserved 1,000 g)
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_gram' => 1400,
            'reserved_stock_gram' => 1000,
            'is_active' => true,
        ]);

        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 500, // Demands 500 g (> 400 g available)
            'is_active' => true,
        ]);

        $this->post('/keranjang', [
            'product_id' => $product->id,
            'weight_option_id' => $weightOption->id,
            'quantity' => 1,
        ]);

        $response = $this->post('/checkout', [
            'customer_name' => 'Pembeli Terlambat',
            'customer_phone' => '081234567890',
            'fulfillment_method' => 'pickup',
        ]);

        $response->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);

        // Verify stock was not changed or partially reserved
        $product->refresh();
        $this->assertEquals(1000, $product->reserved_stock_gram);
    }

    public function test_guest_can_track_order_with_valid_token_and_sees_masked_phone(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock_gram' => 10000, 'is_active' => true]);
        $weightOption = ProductWeightOption::factory()->create(['product_id' => $product->id, 'weight_gram' => 500, 'is_active' => true]);

        $this->post('/keranjang', [
            'product_id' => $product->id,
            'weight_option_id' => $weightOption->id,
            'quantity' => 1,
        ]);

        $this->post('/checkout', [
            'customer_name' => 'Dewi Sartika',
            'customer_phone' => '081234567890',
            'fulfillment_method' => 'pickup',
        ]);

        $order = Order::where('customer_name', 'Dewi Sartika')->first();
        $this->assertNotNull($order);

        $response = $this->get(route('order.track', ['token' => $order->public_tracking_token]));

        $response->assertStatus(200);
        $response->assertSee($order->order_code);
        $response->assertSee('Dewi Sartika');
        $response->assertSee('0812****7890'); // Masked for privacy
        $response->assertSee('Menunggu Pembayaran');
    }

    public function test_tracking_with_invalid_token_returns_404(): void
    {
        $response = $this->get('/pesanan/non-existent-token-12345');

        $response->assertStatus(404);
    }

    public function test_guest_can_checkout_with_cod_payment_method(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Sirloin Segar',
            'sku' => 'DM-SRL-001',
            'price_per_kg' => 140000.00,
            'stock_gram' => 10000,
            'reserved_stock_gram' => 0,
            'is_active' => true,
        ]);
        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 1000,
            'label' => '1 kg',
            'is_active' => true,
        ]);

        $this->post('/keranjang', [
            'product_id' => $product->id,
            'weight_option_id' => $weightOption->id,
            'quantity' => 1,
        ]);

        $response = $this->post('/checkout', [
            'customer_name' => 'Budi COD',
            'customer_phone' => '081299887766',
            'fulfillment_method' => 'pickup',
            'payment_method' => 'cod',
        ]);

        $order = Order::where('customer_name', 'Budi COD')->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('order.track', ['token' => $order->public_tracking_token]));

        $this->assertEquals('cod', $order->payment_method);
        $this->assertEquals(OrderStatus::Processing, $order->order_status);
        $this->assertEquals(PaymentStatus::Unpaid, $order->payment_status);
        $this->assertNull($order->payment_deadline_at);
        $this->assertTrue($order->isCod());

        // Check stock reservation
        $product->refresh();
        $this->assertEquals(1000, $product->reserved_stock_gram);
        $this->assertEquals(10000, $product->stock_gram);
    }

    public function test_cod_order_shows_cod_instructions_on_tracking_page(): void
    {
        $order = Order::factory()->create([
            'customer_name' => 'Pak Joko',
            'payment_method' => 'cod',
            'order_status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Unpaid,
            'total_amount' => 140000.00,
        ]);

        $response = $this->get(route('order.track', ['token' => $order->public_tracking_token]));
        $response->assertStatus(200);
        $response->assertSee('Metode Pembayaran: Bayar di Tempat (COD)');
        $response->assertSee('siapkan uang tunai pas');
        $response->assertDontSee('Bayar Sekarang via Midtrans');
    }

    public function test_sync_payment_status_updates_order_when_settled(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_gram' => 5000,
            'reserved_stock_gram' => 1000,
        ]);

        $order = Order::factory()->create([
            'order_code' => 'DM-TEST-SYNC',
            'payment_method' => 'midtrans',
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
            'total_amount' => 140000.00,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'total_weight_gram' => 1000,
        ]);

        $response = $this->post(route('order.sync_status', ['token' => $order->public_tracking_token]), [
            'result' => [
                'transaction_status' => 'settlement',
                'payment_type' => 'qris',
                'transaction_id' => 'TRX-12345-SYNC',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_paid' => true,
        ]);

        $order->refresh();
        $this->assertEquals(PaymentStatus::Paid, $order->payment_status);
        $this->assertEquals(OrderStatus::Processing, $order->order_status);

        // Physical stock decremented, reserved stock released
        $product->refresh();
        $this->assertEquals(4000, $product->stock_gram);
        $this->assertEquals(0, $product->reserved_stock_gram);
    }
}
