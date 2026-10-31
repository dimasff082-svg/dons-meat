<?php

namespace Database\Seeders;

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Models\ProductWeightOption;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categories
        $catDagingUtuh = Category::firstOrCreate(
            ['slug' => 'daging-utuh'],
            [
                'name' => 'Daging Utuh',
                'description' => 'Potongan daging sapi utuh berkualitas untuk masakan rendang, semur, dendeng, dan gulai.',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $catPotonganKhusus = Category::firstOrCreate(
            ['slug' => 'potongan-khusus'],
            [
                'name' => 'Potongan Khusus',
                'description' => 'Potongan daging sapi premium pilihan seperti tenderloin, sirloin, ribeye, dan saikoro.',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        $catTulang = Category::firstOrCreate(
            ['slug' => 'tulang'],
            [
                'name' => 'Tulang',
                'description' => 'Tulang sapi segar pilihan untuk kaldu gurih, sup iga konro, coto, dan masakan berkuah.',
                'sort_order' => 3,
                'is_active' => true,
            ]
        );

        // 2. Demo Products
        $productsData = [
            [
                'category_id' => $catPotonganKhusus->id,
                'sku' => 'DM-TND01',
                'name' => 'Daging Sapi Has Dalam (Tenderloin)',
                'slug' => 'daging-sapi-has-dalam-tenderloin',
                'description' => 'Bagian daging sapi paling empuk dengan kadar lemak rendah. Sangat cocok untuk steak premium, tumisan cepat, dan shabu-shabu.',
                'price_per_kg' => 175000.00,
                'stock_gram' => 20000,
                'low_stock_threshold_gram' => 3000,
                'is_active' => true,
                'is_featured' => true,
                'weights' => [250, 500, 1000],
            ],
            [
                'category_id' => $catPotonganKhusus->id,
                'sku' => 'DM-SRL01',
                'name' => 'Daging Sapi Has Luar (Sirloin)',
                'slug' => 'daging-sapi-has-luar-sirloin',
                'description' => 'Potongan has luar bertekstur juicy dengan lapisan lemak gurih di tepinya. Pilihan favorit untuk steak beraroma kaya.',
                'price_per_kg' => 160000.00,
                'stock_gram' => 15000,
                'low_stock_threshold_gram' => 2500,
                'is_active' => true,
                'is_featured' => true,
                'weights' => [250, 500, 1000],
            ],
            [
                'category_id' => $catDagingUtuh->id,
                'sku' => 'DM-RND01',
                'name' => 'Daging Sapi Rendang & Semur (Gandik)',
                'slug' => 'daging-sapi-rendang-semur-gandik',
                'description' => 'Daging sapi padat bertekstur serat halus. Sangat tahan dimasak lama untuk rendang, empal, dendeng, dan semur daging.',
                'price_per_kg' => 140000.00,
                'stock_gram' => 30000,
                'low_stock_threshold_gram' => 5000,
                'is_active' => true,
                'is_featured' => true,
                'weights' => [250, 500, 1000, 2000],
            ],
            [
                'category_id' => $catDagingUtuh->id,
                'sku' => 'DM-BRS01',
                'name' => 'Daging Sapi Sandung Lamur (Brisket)',
                'slug' => 'daging-sapi-sandung-lamur-brisket',
                'description' => 'Bagian dada dengan lapisan lemak gurih lembut. Cocok untuk soto daging, rawon gurih, dan olahan berkuah kaya rasa.',
                'price_per_kg' => 135000.00,
                'stock_gram' => 18000,
                'low_stock_threshold_gram' => 3000,
                'is_active' => true,
                'is_featured' => false,
                'weights' => [250, 500, 1000],
            ],
            [
                'category_id' => $catTulang->id,
                'sku' => 'DM-KNR01',
                'name' => 'Tulang Iga Sapi Segar (Konro / Short Ribs)',
                'slug' => 'tulang-iga-sapi-segar-konro',
                'description' => 'Tulang iga sapi berdaging tebal dan empuk. Sangat nikmat diolah menjadi sup konro khas Makassar atau iga bakar pedas manis.',
                'price_per_kg' => 110000.00,
                'stock_gram' => 25000,
                'low_stock_threshold_gram' => 4000,
                'is_active' => true,
                'is_featured' => true,
                'weights' => [500, 1000, 2000],
            ],
            [
                'category_id' => $catTulang->id,
                'sku' => 'DM-SMS01',
                'name' => 'Tulang Sumsum Sapi Segar',
                'slug' => 'tulang-sumsum-sapi-segar',
                'description' => 'Tulang pipa sapi dengan sumsum gurih kaya kalsium dan kolagen untuk kaldu sup lezat dan gurih alami.',
                'price_per_kg' => 95000.00,
                'stock_gram' => 12000,
                'low_stock_threshold_gram' => 2000,
                'is_active' => true,
                'is_featured' => false,
                'weights' => [500, 1000],
            ],
        ];

        foreach ($productsData as $data) {
            $weights = $data['weights'];
            unset($data['weights']);

            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                $data
            );

            // Record initial price history if empty
            if ($product->priceHistories()->count() === 0) {
                ProductPriceHistory::create([
                    'product_id' => $product->id,
                    'old_price_per_kg' => 0,
                    'new_price_per_kg' => $product->price_per_kg,
                    'changed_by_user_id' => null,
                    'reason' => 'Harga awal produk demo',
                ]);
            }

            // Record initial stock movement if empty
            if ($product->stockMovements()->count() === 0 && $product->stock_gram > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'order_id' => null,
                    'created_by_user_id' => null,
                    'movement_type' => StockMovementType::Inbound,
                    'stock_delta_gram' => $product->stock_gram,
                    'reserved_delta_gram' => 0,
                    'stock_balance_after_gram' => $product->stock_gram,
                    'reserved_balance_after_gram' => 0,
                    'reason' => 'Stok awal demo produk',
                ]);
            }

            // Weight options
            foreach ($weights as $index => $weightGram) {
                $label = $weightGram >= 1000
                    ? ($weightGram / 1000).' kg'
                    : $weightGram.' g';

                ProductWeightOption::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'weight_gram' => $weightGram,
                    ],
                    [
                        'label' => $label,
                        'sort_order' => $index,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
