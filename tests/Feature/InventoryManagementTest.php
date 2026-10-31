<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_inventory(): void
    {
        $response = $this->get('/admin/inventory');
        $response->assertRedirect('/admin/login');

        $responseMovements = $this->get('/admin/inventory/movements');
        $responseMovements->assertRedirect('/admin/login');

        $responseAdjust = $this->get('/admin/inventory/adjust');
        $responseAdjust->assertRedirect('/admin/login');
    }

    public function test_admin_and_owner_can_view_inventory_overview(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Daging Sapi']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Sirloin Premium',
            'sku' => 'DM-SIR-001',
            'stock_gram' => 15000,
            'reserved_stock_gram' => 2000,
            'low_stock_threshold_gram' => 5000,
        ]);

        $response = $this->actingAs($admin)->get('/admin/inventory');

        $response->assertStatus(200);
        $response->assertSee('Sirloin Premium');
        $response->assertSee('DM-SIR-001');
        $response->assertSee('15,000');
        $response->assertSee('2,000');
        $response->assertSee('13,000'); // Available stock (15000 - 2000)
    }

    public function test_inventory_filters_low_stock_and_out_of_stock_correctly(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        // Product 1: Safe stock (available 20,000 > threshold 5,000)
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Daging Aman',
            'stock_gram' => 20000,
            'reserved_stock_gram' => 0,
            'low_stock_threshold_gram' => 5000,
        ]);

        // Product 2: Low stock (available 3,000 <= threshold 5,000)
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Daging Menipis',
            'stock_gram' => 4000,
            'reserved_stock_gram' => 1000,
            'low_stock_threshold_gram' => 5000,
        ]);

        // Product 3: Out of stock (available 0)
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Daging Habis',
            'stock_gram' => 2000,
            'reserved_stock_gram' => 2000,
            'low_stock_threshold_gram' => 5000,
        ]);

        // Filter low stock
        $responseLow = $this->actingAs($admin)->get('/admin/inventory?filter=low_stock');
        $responseLow->assertStatus(200);
        $responseLow->assertSee('Daging Menipis');
        $responseLow->assertSee('Daging Habis');
        $responseLow->assertDontSee('Daging Aman');

        // Filter out of stock
        $responseOut = $this->actingAs($admin)->get('/admin/inventory?filter=out_of_stock');
        $responseOut->assertStatus(200);
        $responseOut->assertSee('Daging Habis');
        $responseOut->assertDontSee('Daging Aman');
    }

    public function test_admin_can_record_inbound_stock_and_it_increases_stock_and_logs_movement(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'stock_gram' => 10000,
            'reserved_stock_gram' => 0,
        ]);

        $response = $this->actingAs($admin)->post('/admin/inventory/adjust', [
            'product_id' => $product->id,
            'movement_type' => StockMovementType::Inbound->value,
            'weight_gram' => 5000,
            'reason' => 'Penerimaan pasokan sapi baru dari RPH',
        ]);

        $response->assertRedirect('/admin/inventory');
        $response->assertSessionHas('status');

        $product->refresh();
        $this->assertEquals(15000, $product->stock_gram);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => StockMovementType::Inbound->value,
            'stock_delta_gram' => 5000,
            'reserved_delta_gram' => 0,
            'stock_balance_after_gram' => 15000,
            'created_by_user_id' => $admin->id,
            'reason' => 'Penerimaan pasokan sapi baru dari RPH',
        ]);
    }

    public function test_admin_can_record_adjustment_out_and_damaged_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'stock_gram' => 10000,
            'reserved_stock_gram' => 1000,
        ]);

        // 1. Adjustment out (e.g. audit opname correction)
        $responseOut = $this->actingAs($admin)->post('/admin/inventory/adjust', [
            'product_id' => $product->id,
            'movement_type' => StockMovementType::AdjustmentOut->value,
            'weight_gram' => 1500,
            'reason' => 'Koreksi hasil stock opname fisik',
        ]);

        $responseOut->assertRedirect('/admin/inventory');
        $product->refresh();
        $this->assertEquals(8500, $product->stock_gram);

        // 2. Damaged / spoiled meat
        $responseDamaged = $this->actingAs($admin)->post('/admin/inventory/adjust', [
            'product_id' => $product->id,
            'movement_type' => StockMovementType::Damaged->value,
            'weight_gram' => 500,
            'reason' => 'Daging rusak saat trimming',
        ]);

        $responseDamaged->assertRedirect('/admin/inventory');
        $product->refresh();
        $this->assertEquals(8000, $product->stock_gram);
    }

    public function test_cannot_reduce_stock_below_reserved_stock_gram(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'stock_gram' => 10000,
            'reserved_stock_gram' => 8000, // 8,000 g is reserved for pending online orders!
        ]);

        // Attempting to reduce 3,000 g would leave 7,000 g which is below 8,000 g reserved
        $response = $this->actingAs($admin)->from('/admin/inventory/adjust')->post('/admin/inventory/adjust', [
            'product_id' => $product->id,
            'movement_type' => StockMovementType::AdjustmentOut->value,
            'weight_gram' => 3000,
            'reason' => 'Pengurangan melebihi stok yang dicadangkan',
        ]);

        $response->assertRedirect('/admin/inventory/adjust');
        $response->assertSessionHasErrors('weight_gram');

        $product->refresh();
        $this->assertEquals(10000, $product->stock_gram); // Untouched
        $this->assertDatabaseMissing('stock_movements', [
            'reason' => 'Pengurangan melebihi stok yang dicadangkan',
        ]);
    }

    public function test_stock_movements_page_lists_audit_trail_and_filters(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['name' => 'Tenderloin']);

        StockMovement::create([
            'product_id' => $product->id,
            'created_by_user_id' => $admin->id,
            'movement_type' => StockMovementType::Inbound,
            'stock_delta_gram' => 10000,
            'reserved_delta_gram' => 0,
            'stock_balance_after_gram' => 10000,
            'reserved_balance_after_gram' => 0,
            'reason' => 'Stok awal batch 1',
        ]);

        $response = $this->actingAs($admin)->get('/admin/inventory/movements');

        $response->assertStatus(200);
        $response->assertSee('Tenderloin');
        $response->assertSee('Stok awal batch 1');
        $response->assertSee('+10,000');
    }
}
