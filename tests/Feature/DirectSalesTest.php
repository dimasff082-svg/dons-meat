<?php

namespace Tests\Feature;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductWeightOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectSalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_direct_sales(): void
    {
        $response = $this->get('/admin/direct-sales/create');
        $response->assertRedirect('/admin/login');

        $responsePost = $this->post('/admin/direct-sales', []);
        $responsePost->assertRedirect('/admin/login');

        $responseIndex = $this->get('/admin/direct-sales');
        $responseIndex->assertRedirect('/admin/login');
    }

    public function test_admin_and_owner_can_access_direct_sales_cashier_screen(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Ribeye Cut',
            'stock_gram' => 10000,
            'reserved_stock_gram' => 0,
            'is_active' => true,
        ]);
        ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 500,
            'label' => '500 gram',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/direct-sales/create');

        $response->assertStatus(200);
        $response->assertSee('Ribeye Cut');
        $response->assertSee('500 gram');
    }

    public function test_direct_sale_processes_order_successfully_and_decrements_stock_instantly(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Kasir']);
        $category = Category::factory()->create();

        // Product with Rp 140.000 / kg and 10.000 g stock
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Daging Rawon',
            'sku' => 'DM-RAW-001',
            'price_per_kg' => 140000.00,
            'stock_gram' => 10000,
            'reserved_stock_gram' => 0,
            'is_active' => true,
        ]);

        // Weight option: 500 g -> Price = (140,000 * 500) / 1000 = Rp 70,000
        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 500,
            'label' => '500 g',
            'is_active' => true,
        ]);

        $postData = [
            'customer_name' => 'Pak Joko Widodo',
            'customer_phone' => '081234567890',
            'payment_method' => 'cash',
            'amount_paid' => 100000, // Pays with Rp 100,000 (Kembalian Rp 30,000)
            'customer_note' => 'Potong dadu kecil',
            'items' => [
                [
                    'product_id' => $product->id,
                    'weight_option_id' => $weightOption->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post('/admin/direct-sales', $postData);

        $order = Order::where('customer_name', 'Pak Joko Widodo')->first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('admin.direct-sales.show', $order));

        // 1. Verify Order attributes
        $this->assertEquals(OrderChannel::InStore, $order->channel);
        $this->assertEquals(OrderStatus::Completed, $order->order_status);
        $this->assertEquals(PaymentStatus::Paid, $order->payment_status);
        $this->assertEquals(70000.00, (float) $order->subtotal);
        $this->assertEquals(0.00, (float) $order->delivery_fee);
        $this->assertEquals(70000.00, (float) $order->total_amount);
        $this->assertEquals($admin->id, $order->created_by_user_id);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->completed_at);

        // 2. Verify Order Items Snapshot
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name_snapshot' => 'Daging Rawon',
            'product_sku_snapshot' => 'DM-RAW-001',
            'weight_per_package_gram' => 500,
            'quantity' => 1,
            'total_weight_gram' => 500,
            'price_per_kg_snapshot' => 140000.00,
            'subtotal' => 70000.00,
        ]);

        // 3. Verify Payment Record & Metadata
        $payment = $order->payments()->first();
        $this->assertNotNull($payment);
        $this->assertEquals(70000.00, (float) $payment->amount);
        $this->assertEquals('settlement', $payment->status);
        $this->assertEquals('cash', $payment->payment_type);
        $this->assertEquals(100000, $payment->metadata['amount_paid']);
        $this->assertEquals(30000, $payment->metadata['change']);

        // 4. Verify Physical Stock Decrement & Stock Movement Ledger
        $product->refresh();
        $this->assertEquals(9500, $product->stock_gram); // Reduced by 500 g!
        $this->assertEquals(0, $product->reserved_stock_gram); // No online reservation!

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'created_by_user_id' => $admin->id,
            'movement_type' => StockMovementType::Sale->value,
            'stock_delta_gram' => -500,
            'reserved_delta_gram' => 0,
            'stock_balance_after_gram' => 9500,
        ]);
    }

    public function test_direct_sale_fails_if_requested_weight_exceeds_available_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        // Product has only 400 g available (physical 1,400 g - reserved 1,000 g)
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Daging Terbatas',
            'stock_gram' => 1400,
            'reserved_stock_gram' => 1000,
            'is_active' => true,
        ]);

        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 500, // Demands 500 g, which is > 400 g available!
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/admin/direct-sales', [
            'customer_name' => 'Walk-in',
            'payment_method' => 'cash',
            'amount_paid' => 100000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'weight_option_id' => $weightOption->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('items');

        // Verify no order created and stock remains untouched
        $this->assertDatabaseCount('orders', 0);
        $product->refresh();
        $this->assertEquals(1400, $product->stock_gram);
    }

    public function test_direct_sale_fails_if_cash_amount_paid_is_less_than_subtotal(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price_per_kg' => 100000.00,
            'stock_gram' => 10000,
            'is_active' => true,
        ]);

        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 1000, // Subtotal = 100,000
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/admin/direct-sales', [
            'customer_name' => 'Walk-in',
            'payment_method' => 'cash',
            'amount_paid' => 80000, // Insufficient cash!
            'items' => [
                [
                    'product_id' => $product->id,
                    'weight_option_id' => $weightOption->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('amount_paid');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_admin_can_view_and_print_direct_sale_receipt(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Daging Sop Super',
            'sku' => 'DM-SOP-001',
            'price_per_kg' => 120000.00,
            'stock_gram' => 10000,
        ]);
        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 500,
        ]);

        // Process a direct sale
        $this->actingAs($admin)->post('/admin/direct-sales', [
            'customer_name' => 'Ibu Fatimah',
            'customer_phone' => '085299887766',
            'payment_method' => 'cash',
            'amount_paid' => 100000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'weight_option_id' => $weightOption->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $order = Order::where('customer_name', 'Ibu Fatimah')->first();
        $this->assertNotNull($order);

        $response = $this->actingAs($admin)->get(route('admin.direct-sales.show', $order));

        $response->assertStatus(200);
        $response->assertSee($order->order_code);
        $response->assertSee('Ibu Fatimah');
        $response->assertSee('Daging Sop Super');
        $response->assertSee('LUNAS');
        $response->assertSee('Kembalian');
    }

    public function test_direct_sales_history_index_lists_transactions_and_metrics(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price_per_kg' => 100000.00,
            'stock_gram' => 10000,
        ]);
        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 1000,
        ]);

        // Create 1 direct sale
        $this->actingAs($admin)->post('/admin/direct-sales', [
            'customer_name' => 'Budi Santoso',
            'payment_method' => 'cash',
            'amount_paid' => 100000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'weight_option_id' => $weightOption->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response = $this->actingAs($admin)->get('/admin/direct-sales');

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('Rp 100.000');
    }

    public function test_direct_sale_accepts_formatted_amount_paid_with_thousand_dots(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price_per_kg' => 175000.00,
            'stock_gram' => 5000,
        ]);
        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 250, // 250g of 175.000 = Rp 43.750
        ]);

        $response = $this->actingAs($admin)->post('/admin/direct-sales', [
            'customer_name' => 'Pelanggan 43750',
            'payment_method' => 'cash',
            'amount_paid' => '43.750', // Formatted input with dot
            'items' => [
                [
                    'product_id' => $product->id,
                    'weight_option_id' => $weightOption->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $order = Order::where('customer_name', 'Pelanggan 43750')->first();
        $this->assertNotNull($order);
        $this->assertEquals(43750.00, (float) $order->total_amount);
        $this->assertEquals(43750, $order->payments()->first()->metadata['amount_paid']);
        $this->assertEquals(0, $order->payments()->first()->metadata['change']);
        $this->assertEquals(PaymentStatus::Paid, $order->payment_status);
        $response->assertRedirect(route('admin.direct-sales.show', $order));
    }
}
