<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_category_list(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Daging Utuh']);

        $response = $this->actingAs($admin)->get('/admin/categories');

        $response->assertStatus(200);
        $response->assertSee('Daging Utuh');
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Potongan Khusus',
            'description' => 'Potongan steak premium',
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', [
            'name' => 'Potongan Khusus',
            'slug' => 'potongan-khusus',
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Kategori Lama']);

        $response = $this->actingAs($admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Kategori Baru Diupdate',
            'slug' => 'kategori-baru-diupdate',
            'description' => 'Deskripsi baru',
            'sort_order' => 2,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Kategori Baru Diupdate',
        ]);
    }

    public function test_admin_cannot_delete_category_with_existing_products(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->delete("/admin/categories/{$category->id}");

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_view_product_list(): void
    {
        $admin = User::factory()->admin()->create();
        Product::factory()->create(['name' => 'Daging Sirloin']);

        $response = $this->actingAs($admin)->get('/admin/products');

        $response->assertStatus(200);
        $response->assertSee('Daging Sirloin');
    }

    public function test_admin_can_create_product_with_weight_options_and_price_history(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $image = UploadedFile::fake()->create('tenderloin.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($admin)->post('/admin/products', [
            'category_id' => $category->id,
            'sku' => 'DM-TND09',
            'name' => 'Daging Tenderloin Premium',
            'description' => 'Sangat empuk',
            'price_per_kg' => 175000,
            'stock_gram' => 20000,
            'low_stock_threshold_gram' => 3000,
            'is_active' => 1,
            'is_featured' => 1,
            'image' => $image,
            'default_weights' => [250, 500, 1000],
        ]);

        $response->assertRedirect('/admin/products');

        $product = Product::where('sku', 'DM-TND09')->first();
        $this->assertNotNull($product);
        $this->assertEquals(175000, (float) $product->price_per_kg);
        $this->assertEquals(3, $product->weightOptions()->count());

        // Check initial price history
        $this->assertDatabaseHas('product_price_histories', [
            'product_id' => $product->id,
            'new_price_per_kg' => 175000,
        ]);

        // Check initial stock movement
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'stock_delta_gram' => 20000,
        ]);
    }

    public function test_admin_updating_product_price_records_product_price_history(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price_per_kg' => 140000.00,
        ]);

        $response = $this->actingAs($admin)->put("/admin/products/{$product->id}", [
            'category_id' => $category->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price_per_kg' => 155000.00,
            'low_stock_threshold_gram' => 2000,
            'is_active' => 1,
            'is_featured' => 0,
            'price_change_reason' => 'Penyesuaian harga pasar karkas',
        ]);

        $response->assertRedirect('/admin/products');

        $this->assertDatabaseHas('product_price_histories', [
            'product_id' => $product->id,
            'old_price_per_kg' => 140000.00,
            'new_price_per_kg' => 155000.00,
            'changed_by_user_id' => $admin->id,
            'reason' => 'Penyesuaian harga pasar karkas',
        ]);
    }

    public function test_admin_can_add_weight_option_to_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->post("/admin/products/{$product->id}/weight-options", [
            'weight_gram' => 750,
            'label' => '750 g',
            'is_active' => 1,
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('product_weight_options', [
            'product_id' => $product->id,
            'weight_gram' => 750,
            'label' => '750 g',
        ]);
    }

    public function test_owner_cannot_access_store_settings_route(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->get('/admin/settings');

        // Forbidden: Owner only receives reports per revised specifications
        $response->assertStatus(403);
    }

    public function test_admin_can_update_store_settings_including_standard_beef_price(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->put('/admin/settings', [
            'business_name' => 'DONS MEAT PUSAT',
            'standard_beef_price_per_kg' => 150000.00,
            'business_description' => 'Daging sapi terbaik',
            'phone' => '08999999999',
            'email' => 'admin@donsmeat.local',
            'address' => 'Makassar',
            'opening_hours' => 'Setiap Hari 08.00 - 18.00',
            'payment_expiry_minutes' => 45,
            'timezone' => 'Asia/Makassar',
        ]);

        $response->assertRedirect(route('admin.settings.edit'));
        $this->assertDatabaseHas('store_settings', [
            'business_name' => 'DONS MEAT PUSAT',
            'standard_beef_price_per_kg' => 150000.00,
            'payment_expiry_minutes' => 45,
        ]);
    }
}
