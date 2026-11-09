<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductWeightOption;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_standard_beef_price_from_database(): void
    {
        StoreSetting::current()->update([
            'business_name' => 'DONS MEAT TEST',
            'standard_beef_price_per_kg' => 145000.00,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('DONS MEAT TEST');
        $response->assertSee('145.000');
    }

    public function test_catalog_index_displays_products_and_categories(): void
    {
        $category = Category::factory()->create(['name' => 'Daging Spesial', 'slug' => 'daging-spesial', 'is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Ribeye Cut',
            'slug' => 'ribeye-cut',
            'price_per_kg' => 180000.00,
            'is_active' => true,
        ]);

        $response = $this->get('/katalog');

        $response->assertStatus(200);
        $response->assertSee('Ribeye Cut');
        $response->assertSee('Daging Spesial');
    }

    public function test_catalog_can_be_filtered_by_category(): void
    {
        $cat1 = Category::factory()->create(['name' => 'Kategori Satu', 'slug' => 'kategori-satu', 'is_active' => true]);
        $cat2 = Category::factory()->create(['name' => 'Kategori Dua', 'slug' => 'kategori-dua', 'is_active' => true]);

        $prod1 = Product::factory()->create([
            'category_id' => $cat1->id,
            'name' => 'Produk Kategori Satu',
            'is_active' => true,
        ]);
        $prod2 = Product::factory()->create([
            'category_id' => $cat2->id,
            'name' => 'Produk Kategori Dua',
            'is_active' => true,
        ]);

        $response = $this->get('/katalog?kategori=kategori-satu');

        $response->assertStatus(200);
        $response->assertSee('Produk Kategori Satu');
        $response->assertDontSee('Produk Kategori Dua');
    }

    public function test_catalog_can_be_searched_by_keyword(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Daging Rendang Spesial',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Tulang Sumsum Super',
            'is_active' => true,
        ]);

        $response = $this->get('/katalog?q=Rendang');

        $response->assertStatus(200);
        $response->assertSee('Daging Rendang Spesial');
        $response->assertDontSee('Tulang Sumsum Super');
    }

    public function test_product_detail_page_displays_weight_options_and_price_per_kg(): void
    {
        $category = Category::factory()->create(['name' => 'Daging Utuh', 'is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Daging Gandik',
            'slug' => 'daging-gandik',
            'sku' => 'DM-GND01',
            'price_per_kg' => 140000.00,
            'stock_gram' => 20000,
            'reserved_stock_gram' => 0,
            'is_active' => true,
        ]);

        ProductWeightOption::factory()->create([
            'product_id' => $product->id,
            'weight_gram' => 500,
            'label' => '500 g',
            'is_active' => true,
        ]);

        $response = $this->get('/katalog/daging-gandik');

        $response->assertStatus(200);
        $response->assertSee('Daging Gandik');
        $response->assertSee('DM-GND01');
        $response->assertSee('140.000');
        $response->assertSee('500 g');
    }

    public function test_store_profile_page_displays_business_information(): void
    {
        StoreSetting::current()->update([
            'business_name' => 'DONS MEAT MAKASSAR',
            'standard_beef_price_per_kg' => 140000.00,
            'address' => 'Jl. Pengayoman No. 12',
        ]);

        $response = $this->get('/tentang');

        $response->assertStatus(200);
        $response->assertSee('DONS MEAT MAKASSAR');
        $response->assertSee('Jl. Pengayoman No. 12');
        $response->assertSee('140.000');
        $response->assertSee('Sertifikasi Halal Indonesia');
        $response->assertSee('Sertifikasi NKV Resmi');
        $response->assertSee('Iga Premium Sapi Lokal');
    }
}
