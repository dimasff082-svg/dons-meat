<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductWeightOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_empty_cart_page(): void
    {
        $response = $this->get('/keranjang');

        $response->assertStatus(200);
        $response->assertSee('Keranjang Belanja Anda Kosong');
    }

    public function test_user_can_add_product_to_cart(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Daging Rendang Spesial',
            'price_per_kg' => 135000.00,
            'stock_gram' => 10000,
            'is_active' => true,
        ]);
        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 500,
            'label' => '500 gram',
            'is_active' => true,
        ]);

        $response = $this->post('/keranjang', [
            'product_id' => $product->id,
            'weight_option_id' => $weightOption->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect('/keranjang');
        $response->assertSessionHas('status');

        $cartResponse = $this->get('/keranjang');
        $cartResponse->assertStatus(200);
        $cartResponse->assertSee('Daging Rendang Spesial');
        $cartResponse->assertSee('500 gram');
        // 2 x 500g = 1,000g; price = (135,000 * 500 / 1000) * 2 = Rp 135,000
        $cartResponse->assertSee('135.000');
    }

    public function test_user_can_update_quantity_in_cart(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 500,
            'is_active' => true,
        ]);

        // Add 1 item
        $this->post('/keranjang', [
            'product_id' => $product->id,
            'weight_option_id' => $weightOption->id,
            'quantity' => 1,
        ]);

        // Update quantity to 3
        $response = $this->patch("/keranjang/{$weightOption->id}", [
            'quantity' => 3,
        ]);

        $response->assertRedirect('/keranjang');

        $sessionCart = session()->get('dons_meat_cart');
        $this->assertEquals(3, $sessionCart[$weightOption->id]['quantity']);
    }

    public function test_user_can_remove_item_from_cart(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 500,
            'is_active' => true,
        ]);

        $this->post('/keranjang', [
            'product_id' => $product->id,
            'weight_option_id' => $weightOption->id,
            'quantity' => 1,
        ]);

        $response = $this->delete("/keranjang/{$weightOption->id}");
        $response->assertRedirect('/keranjang');

        $sessionCart = session()->get('dons_meat_cart');
        $this->assertArrayNotHasKey($weightOption->id, $sessionCart);
    }

    public function test_user_can_clear_all_items_from_cart(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 500,
            'is_active' => true,
        ]);

        $this->post('/keranjang', [
            'product_id' => $product->id,
            'weight_option_id' => $weightOption->id,
            'quantity' => 2,
        ]);

        $response = $this->delete('/keranjang');
        $response->assertRedirect('/keranjang');

        $this->assertNull(session()->get('dons_meat_cart'));
    }

    public function test_cannot_add_inactive_product_or_weight_option(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => false, // Inactive product!
        ]);
        $weightOption = ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 500,
            'is_active' => true,
        ]);

        $response = $this->post('/keranjang', [
            'product_id' => $product->id,
            'weight_option_id' => $weightOption->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertEmpty(session()->get('dons_meat_cart', []));
    }
}
