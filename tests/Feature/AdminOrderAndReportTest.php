<?php

namespace Tests\Feature;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminOrderAndReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_orders_or_reports(): void
    {
        $this->get('/admin/orders')->assertRedirect('/admin/login');
        $this->get('/admin/reports/sales')->assertRedirect('/admin/login');
        $this->get('/admin/reports/low-stock')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_orders_list_with_filters(): void
    {
        $admin = User::factory()->admin()->create();

        $orderOnline = Order::factory()->create([
            'order_code' => 'ORD-ONLINE-001',
            'channel' => OrderChannel::Online,
            'order_status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Paid,
            'customer_name' => 'Budi Santoso',
        ]);

        $orderInStore = Order::factory()->create([
            'order_code' => 'ORD-INSTORE-002',
            'channel' => OrderChannel::InStore,
            'order_status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'customer_name' => 'Pelanggan Toko Langsung',
        ]);

        $response = $this->actingAs($admin)->get('/admin/orders');
        $response->assertStatus(200);
        $response->assertSee('ORD-ONLINE-001');
        $response->assertSee('ORD-INSTORE-002');

        // Test filtering by status
        $responseFiltered = $this->actingAs($admin)->get('/admin/orders?status=processing');
        $responseFiltered->assertStatus(200);
        $responseFiltered->assertSee('ORD-ONLINE-001');
        $responseFiltered->assertDontSee('ORD-INSTORE-002');
    }

    public function test_admin_can_view_order_details(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['name' => 'Daging Tenderloin Super']);

        $order = Order::factory()->create([
            'order_code' => 'ORD-DETAIL-001',
            'customer_name' => 'Siti Rahma',
            'customer_phone' => '08123456789',
            'total_amount' => 175000.00,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name_snapshot' => 'Daging Tenderloin Super',
            'subtotal' => 175000.00,
        ]);

        $response = $this->actingAs($admin)->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee('ORD-DETAIL-001');
        $response->assertSee('Siti Rahma');
        $response->assertSee('Daging Tenderloin Super');
        $response->assertSee('175.000');
    }

    public function test_admin_can_update_fulfillment_status_following_valid_transitions(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Pickup Order: processing -> ready_for_pickup -> completed
        $pickupOrder = Order::factory()->create([
            'order_code' => 'ORD-PICKUP-001',
            'fulfillment_method' => FulfillmentMethod::Pickup,
            'order_status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $response1 = $this->actingAs($admin)->patch("/admin/orders/{$pickupOrder->id}/fulfillment", [
            'order_status' => OrderStatus::ReadyForPickup->value,
            'note' => 'Daging sudah dipotong dan dikemas di freezer toko',
        ]);
        $response1->assertRedirect();

        $pickupOrder->refresh();
        $this->assertEquals(OrderStatus::ReadyForPickup, $pickupOrder->order_status);

        // Next transition: ready_for_pickup -> completed
        $response2 = $this->actingAs($admin)->patch("/admin/orders/{$pickupOrder->id}/fulfillment", [
            'order_status' => OrderStatus::Completed->value,
            'note' => 'Diambil oleh pelanggan di counter',
        ]);
        $response2->assertRedirect();

        $pickupOrder->refresh();
        $this->assertEquals(OrderStatus::Completed, $pickupOrder->order_status);
        $this->assertNotNull($pickupOrder->completed_at);

        // 2. Delivery Order: processing -> on_delivery -> completed
        $deliveryOrder = Order::factory()->create([
            'order_code' => 'ORD-DELIVERY-001',
            'fulfillment_method' => FulfillmentMethod::Delivery,
            'order_status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $response3 = $this->actingAs($admin)->patch("/admin/orders/{$deliveryOrder->id}/fulfillment", [
            'order_status' => OrderStatus::OnDelivery->value,
        ]);
        $response3->assertRedirect();

        $deliveryOrder->refresh();
        $this->assertEquals(OrderStatus::OnDelivery, $deliveryOrder->order_status);
    }

    public function test_admin_cannot_make_invalid_status_transition(): void
    {
        $admin = User::factory()->admin()->create();

        $completedOrder = Order::factory()->create([
            'order_status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        // Attempt to move back to processing
        $response = $this->actingAs($admin)->patch("/admin/orders/{$completedOrder->id}/fulfillment", [
            'order_status' => OrderStatus::Processing->value,
        ]);

        $response->assertSessionHasErrors('order_status');
        $completedOrder->refresh();
        $this->assertEquals(OrderStatus::Completed, $completedOrder->order_status);
    }

    public function test_admin_cannot_cancel_paid_order(): void
    {
        $admin = User::factory()->admin()->create();

        $paidOrder = Order::factory()->create([
            'order_status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $response = $this->actingAs($admin)->post("/admin/orders/{$paidOrder->id}/cancel", [
            'cancellation_reason' => 'Ingin dibatalkan',
        ]);

        $response->assertSessionHasErrors('order');

        $paidOrder->refresh();
        $this->assertEquals(OrderStatus::Processing, $paidOrder->order_status);
        $this->assertEquals(PaymentStatus::Paid, $paidOrder->payment_status);
    }

    public function test_admin_can_cancel_unpaid_order_and_reserved_stock_is_safely_released(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'stock_gram' => 5000,
            'reserved_stock_gram' => 1500,
        ]);

        $unpaidOrder = Order::factory()->create([
            'order_code' => 'ORD-CANCEL-ME',
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        OrderItem::factory()->create([
            'order_id' => $unpaidOrder->id,
            'product_id' => $product->id,
            'total_weight_gram' => 1500,
        ]);

        $response = $this->actingAs($admin)->post("/admin/orders/{$unpaidOrder->id}/cancel", [
            'cancellation_reason' => 'Pelanggan meminta dibatalkan karena salah alamat',
        ]);

        $response->assertRedirect();

        $unpaidOrder->refresh();
        $this->assertEquals(OrderStatus::Cancelled, $unpaidOrder->order_status);
        $this->assertEquals(PaymentStatus::Cancelled, $unpaidOrder->payment_status);

        // Reserved stock must be released! Physical stock untouched.
        $product->refresh();
        $this->assertEquals(5000, $product->stock_gram);
        $this->assertEquals(0, $product->reserved_stock_gram);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'order_id' => $unpaidOrder->id,
            'movement_type' => StockMovementType::ReleaseReservation->value,
            'stock_delta_gram' => 0,
            'reserved_delta_gram' => -1500,
        ]);
    }

    public function test_admin_can_view_sales_report_and_metrics_are_accurate(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Paid Online Order (Rp 100.000)
        Order::factory()->create([
            'channel' => OrderChannel::Online,
            'payment_status' => PaymentStatus::Paid,
            'order_status' => OrderStatus::Processing,
            'total_amount' => 100000.00,
            'paid_at' => now(),
            'created_at' => now(),
        ]);

        // 2. Paid In-Store Order (Rp 250.000)
        Order::factory()->create([
            'channel' => OrderChannel::InStore,
            'payment_status' => PaymentStatus::Paid,
            'order_status' => OrderStatus::Completed,
            'total_amount' => 250000.00,
            'paid_at' => now(),
            'created_at' => now(),
        ]);

        // 3. Unpaid Order (Rp 500.000) - MUST NOT be counted in omzet!
        Order::factory()->create([
            'channel' => OrderChannel::Online,
            'payment_status' => PaymentStatus::Unpaid,
            'order_status' => OrderStatus::PendingPayment,
            'total_amount' => 500000.00,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports/sales?start_date='.now()->toDateString().'&end_date='.now()->toDateString());

        $response->assertStatus(200);
        // Total Omzet should be 100.000 + 250.000 = 350.000 (NOT 850.000)
        $response->assertSee('350.000');
        $response->assertSee('100.000');
        $response->assertSee('250.000');
    }

    public function test_admin_can_export_sales_report_csv(): void
    {
        $admin = User::factory()->admin()->create();

        Order::factory()->create([
            'order_code' => 'ORD-CSV-999',
            'customer_name' => 'Pelanggan CSV Test',
            'total_amount' => 125000.00,
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports/sales/export');

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type') ?? '', 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition') ?? '', 'attachment'));
    }

    public function test_admin_can_view_low_stock_report(): void
    {
        $admin = User::factory()->admin()->create();

        $lowProduct = Product::factory()->create([
            'name' => 'Daging Rawon Kritis',
            'stock_gram' => 2000,
            'reserved_stock_gram' => 1000, // available = 1000g
            'low_stock_threshold_gram' => 3000, // threshold = 3000g -> LOW!
        ]);

        $normalProduct = Product::factory()->create([
            'name' => 'Daging Rendang Melimpah',
            'stock_gram' => 50000,
            'reserved_stock_gram' => 0,
            'low_stock_threshold_gram' => 5000, // available = 50000g > 5000g -> SAFE
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports/low-stock');

        $response->assertStatus(200);
        $response->assertSee('Daging Rawon Kritis');
        $response->assertDontSee('Daging Rendang Melimpah');
    }

    public function test_dashboard_displays_accurate_counters_and_low_stock_alert(): void
    {
        $admin = User::factory()->admin()->create();

        Product::factory()->create([
            'name' => 'Produk Kritis Dashboard',
            'stock_gram' => 500,
            'reserved_stock_gram' => 0,
            'low_stock_threshold_gram' => 2000,
        ]);

        Order::factory()->create([
            'order_status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Paid,
            'total_amount' => 75000.00,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Dashboard');
        $response->assertSee('Peringatan Persediaan');
        $response->assertSee('Perlu Disiapkan');
    }

    public function test_admin_or_cashier_completing_cod_order_marks_paid_and_decrements_stock(): void
    {
        $cashier = User::factory()->cashier()->create();
        $product = Product::factory()->create([
            'stock_gram' => 5000,
            'reserved_stock_gram' => 1000,
        ]);

        $order = Order::factory()->create([
            'fulfillment_method' => FulfillmentMethod::Pickup,
            'payment_method' => 'cod',
            'order_status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Unpaid,
            'total_amount' => 140000.00,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'total_weight_gram' => 1000,
        ]);

        $response = $this->actingAs($cashier)->patch(route('admin.orders.fulfillment', $order), [
            'order_status' => OrderStatus::Completed->value,
            'note' => 'Daging diserahkan & pembayaran tunai diterima di kasir',
        ]);

        $response->assertSessionHasNoErrors();
        $order->refresh();
        $this->assertEquals(OrderStatus::Completed, $order->order_status);
        $this->assertEquals(PaymentStatus::Paid, $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->completed_at);

        // Physical stock decremented and reserved released
        $product->refresh();
        $this->assertEquals(4000, $product->stock_gram);
        $this->assertEquals(0, $product->reserved_stock_gram);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'movement_type' => StockMovementType::Sale->value,
            'stock_delta_gram' => -1000,
            'reserved_delta_gram' => -1000,
        ]);
    }

    public function test_owner_can_export_low_stock_report_to_csv_as_restock_reference(): void
    {
        $owner = User::factory()->owner()->create();

        Product::factory()->create([
            'name' => 'Daging Sup Menipis',
            'sku' => 'DM-SUP-99',
            'stock_gram' => 800,
            'reserved_stock_gram' => 0,
            'low_stock_threshold_gram' => 2000,
        ]);

        $response = $this->actingAs($owner)->get(route('admin.reports.low-stock.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('laporan-stok-menipis-dons-meat-', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_admin_and_cashier_can_view_order_print_label_and_receipt(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create([
            'order_code' => 'DM-PRINT-001',
            'customer_name' => 'Pak Haji Amiruddin',
            'customer_phone' => '081299887766',
            'delivery_address' => 'Jl. Boulevard No. 88, Panakkukang, Makassar',
            'fulfillment_method' => FulfillmentMethod::Delivery,
            'order_status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Paid,
            'customer_note' => 'Pagar abu-abu sebelah toko bunga',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => Product::factory()->create()->id,
            'product_name_snapshot' => 'Iga Konro Segar',
            'product_sku_snapshot' => 'DM-KNR-01',
            'weight_per_package_gram' => 500,
            'quantity' => 2,
            'total_weight_gram' => 1000,
            'price_per_kg_snapshot' => 110000,
            'subtotal' => 110000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.print', $order));

        $response->assertStatus(200);
        $response->assertSee('DM-PRINT-001');
        $response->assertSee('Pak Haji Amiruddin');
        $response->assertSee('081299887766');
        $response->assertSee('Jl. Boulevard No. 88');
        $response->assertSee('Iga Konro Segar');
        $response->assertSee('TUJUAN PENGANTARAN');
        $response->assertSee('SUDAH LUNAS');
    }

    public function test_admin_can_update_store_settings_including_logo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $logoFile = UploadedFile::fake()->image('custom_logo.png', 200, 200);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'business_name' => 'DONS MEAT PUSAT',
            'standard_beef_price_per_kg' => 140000,
            'business_description' => 'Pusat aneka daging terpercaya',
            'phone' => '08111222333',
            'email' => 'halo@donsmeat.id',
            'address' => 'Makassar Kota',
            'opening_hours' => '08.00 - 18.00 WITA',
            'payment_expiry_minutes' => 60,
            'timezone' => 'Asia/Makassar',
            'logo' => $logoFile,
        ]);

        $response->assertRedirect(route('admin.settings.edit'));
        $response->assertSessionHas('status');

        $setting = StoreSetting::current();
        $this->assertEquals('DONS MEAT PUSAT', $setting->business_name);
        $this->assertNotNull($setting->logo_path);
        Storage::disk('public')->assertExists($setting->logo_path);
    }

    public function test_owner_and_admin_can_view_overall_sales_history_and_export_csv(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $cashier = User::factory()->cashier()->create();

        Order::factory()->create([
            'order_code' => 'ORD-ALL-001',
            'channel' => OrderChannel::InStore,
            'order_status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'created_by_user_id' => $cashier->id,
            'total_amount' => 150000,
        ]);

        // Owner can access
        $ownerResponse = $this->actingAs($owner)->get(route('admin.sales-history.index'));
        $ownerResponse->assertStatus(200);
        $ownerResponse->assertSee('ORD-ALL-001');
        $ownerResponse->assertSee('Riwayat Seluruh Penjualan');

        // Admin can access
        $adminResponse = $this->actingAs($admin)->get(route('admin.sales-history.index'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('ORD-ALL-001');

        // Export CSV
        $exportResponse = $this->actingAs($owner)->get(route('admin.sales-history.export'));
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('ORD-ALL-001', $exportResponse->streamedContent());
    }

    public function test_cashier_cannot_access_overall_sales_history_but_can_view_personal_sales_history(): void
    {
        $cashierA = User::factory()->cashier()->create(['name' => 'Kasir Siti']);
        $cashierB = User::factory()->cashier()->create(['name' => 'Kasir Budi']);

        Order::factory()->create([
            'order_code' => 'ORD-KASIR-A',
            'channel' => OrderChannel::InStore,
            'order_status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'created_by_user_id' => $cashierA->id,
            'total_amount' => 75000,
        ]);

        Order::factory()->create([
            'order_code' => 'ORD-KASIR-B',
            'channel' => OrderChannel::InStore,
            'order_status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'created_by_user_id' => $cashierB->id,
            'total_amount' => 95000,
        ]);

        // Cashier cannot access overall history (403)
        $forbiddenResponse = $this->actingAs($cashierA)->get(route('admin.sales-history.index'));
        $forbiddenResponse->assertStatus(403);

        // Cashier can access personal history
        $personalResponse = $this->actingAs($cashierA)->get(route('admin.my-sales-history.index'));
        $personalResponse->assertStatus(200);
        $personalResponse->assertSee('ORD-KASIR-A');
        $personalResponse->assertDontSee('ORD-KASIR-B');

        // Export personal CSV
        $exportResponse = $this->actingAs($cashierA)->get(route('admin.my-sales-history.export'));
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('ORD-KASIR-A', $exportResponse->streamedContent());
        $this->assertStringNotContainsString('ORD-KASIR-B', $exportResponse->streamedContent());
    }
}
