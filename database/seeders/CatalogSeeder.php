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
        $catDagingSapi = Category::updateOrCreate(
            ['slug' => 'daging-sapi'],
            [
                'name' => 'Daging Sapi',
                'description' => 'Aneka potongan daging sapi segar pilihan berkualitas untuk rendang, semur, empal, dendeng, dan masakan keluarga.',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $catAyam = Category::updateOrCreate(
            ['slug' => 'daging-ayam-unggas'],
            [
                'name' => 'Daging Ayam & Unggas',
                'description' => 'Daging ayam segar higienis, fillet dada, paha utuh, sayap, dan karkas utuh untuk kebutuhan harian & kuliner.',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        $catKambing = Category::updateOrCreate(
            ['slug' => 'daging-kambing-domba'],
            [
                'name' => 'Daging Kambing & Domba',
                'description' => 'Daging kambing muda segar dan empuk untuk sate, tongseng, gulai rempah, sup, dan kari lezat.',
                'sort_order' => 3,
                'is_active' => true,
            ]
        );

        $catPotonganKhusus = Category::updateOrCreate(
            ['slug' => 'potongan-khusus'],
            [
                'name' => 'Potongan Khusus & Olahan',
                'description' => 'Potongan daging premium seperti tenderloin, sirloin, slice shabu/sukiyaki, dan daging giling higienis.',
                'sort_order' => 4,
                'is_active' => true,
            ]
        );

        $catTulang = Category::updateOrCreate(
            ['slug' => 'tulang'],
            [
                'name' => 'Tulang & Sup Gurih',
                'description' => 'Tulang sapi segar pilihan, iga konro, dan sumsum gurih untuk kaldu lezat dan aneka masakan berkuah.',
                'sort_order' => 5,
                'is_active' => true,
            ]
        );

        // Migrate any legacy 'daging-utuh' products to 'daging-sapi'
        $legacyCategory = Category::where('slug', 'daging-utuh')->first();
        if ($legacyCategory && $legacyCategory->id !== $catDagingSapi->id) {
            Product::where('category_id', $legacyCategory->id)->update(['category_id' => $catDagingSapi->id]);
            $legacyCategory->delete();
        }

        // 2. Demo Products (Beef, Chicken, Goat, Special Cuts, Bones)
        $productsData = [
            // Daging Sapi
            [
                'category_id' => $catDagingSapi->id,
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
                'category_id' => $catDagingSapi->id,
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

            // Daging Ayam & Unggas
            [
                'category_id' => $catAyam->id,
                'sku' => 'DM-AYM01',
                'name' => 'Fillet Dada Ayam Segar (Boneless Breast)',
                'slug' => 'fillet-dada-ayam-segar-boneless',
                'description' => 'Dada ayam tanpa tulang dan tanpa kulit, dipotong higienis setiap hari. Sangat cocok untuk olahan sehat, gym diet, chicken steak, dan katsu.',
                'price_per_kg' => 55000.00,
                'stock_gram' => 40000,
                'low_stock_threshold_gram' => 5000,
                'is_active' => true,
                'is_featured' => true,
                'weights' => [250, 500, 1000, 2000],
            ],
            [
                'category_id' => $catAyam->id,
                'sku' => 'DM-AYM02',
                'name' => 'Paha Ayam Utuh Segar (Thigh & Drumstick)',
                'slug' => 'paha-ayam-utuh-segar',
                'description' => 'Potongan paha ayam utuh juicy berlemak gurih. Pilihan utama untuk ayam bakar madu, ayam goreng kremes, rica-rica, dan opor.',
                'price_per_kg' => 48000.00,
                'stock_gram' => 35000,
                'low_stock_threshold_gram' => 5000,
                'is_active' => true,
                'is_featured' => false,
                'weights' => [500, 1000, 2000],
            ],
            [
                'category_id' => $catAyam->id,
                'sku' => 'DM-AYM03',
                'name' => 'Ayam Karkas Utuh Segar (Whole Chicken)',
                'slug' => 'ayam-karkas-utuh-segar',
                'description' => 'Ayam potong utuh bersih tanpa jeroan, higienis, segar, dan bersertifikasi halal. Sangat pas untuk ayam panggang utuh, sup ayam kampung, atau hidangan kenduri.',
                'price_per_kg' => 42000.00,
                'stock_gram' => 25000,
                'low_stock_threshold_gram' => 4000,
                'is_active' => true,
                'is_featured' => false,
                'weights' => [1000, 2000],
            ],

            // Daging Kambing & Domba
            [
                'category_id' => $catKambing->id,
                'sku' => 'DM-KMB01',
                'name' => 'Daging Paha Kambing Muda Segar',
                'slug' => 'daging-paha-kambing-muda-segar',
                'description' => 'Potongan paha belakang kambing muda pilihan dengan tekstur empuk dan minim bau prengus. Ideal untuk sate kambing empuk, kambing guling, dan krengsengan.',
                'price_per_kg' => 165000.00,
                'stock_gram' => 20000,
                'low_stock_threshold_gram' => 3000,
                'is_active' => true,
                'is_featured' => true,
                'weights' => [250, 500, 1000],
            ],
            [
                'category_id' => $catKambing->id,
                'sku' => 'DM-KMB02',
                'name' => 'Daging Kambing Gulai & Tongseng',
                'slug' => 'daging-kambing-gulai-tongseng',
                'description' => 'Potongan daging kambing campur sedikit lemak gurih beraroma mantap. Paling pas dimasak tongseng kuah kental, gulai rempah, kari, dan sop kambing.',
                'price_per_kg' => 145000.00,
                'stock_gram' => 22000,
                'low_stock_threshold_gram' => 3500,
                'is_active' => true,
                'is_featured' => false,
                'weights' => [500, 1000],
            ],

            // Potongan Khusus & Olahan
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
                'category_id' => $catPotonganKhusus->id,
                'sku' => 'DM-SLI01',
                'name' => 'Daging Sapi Iris Tipis (Shortplate Shabu / Sukiyaki)',
                'slug' => 'daging-sapi-iris-tipis-shortplate-shabu',
                'description' => 'Irisan daging sapi tipis (slice 1.5mm - 2mm) dengan gurih marbling seimbang. Cepat matang, sangat nikmat untuk shabu-shabu, sukiyaki, yakiniku, dan beef bowl.',
                'price_per_kg' => 150000.00,
                'stock_gram' => 25000,
                'low_stock_threshold_gram' => 4000,
                'is_active' => true,
                'is_featured' => true,
                'weights' => [250, 500, 1000],
            ],
            [
                'category_id' => $catPotonganKhusus->id,
                'sku' => 'DM-GLG01',
                'name' => 'Daging Sapi Giling Spesial (Minced Beef)',
                'slug' => 'daging-sapi-giling-spesial-minced-beef',
                'description' => 'Daging sapi segar digiling halus dengan rasio 85% daging dan 15% lemak untuk tekstur empuk maksimal. Cocok untuk bakso, patty burger, bolognese, dan pastel.',
                'price_per_kg' => 135000.00,
                'stock_gram' => 20000,
                'low_stock_threshold_gram' => 3000,
                'is_active' => true,
                'is_featured' => false,
                'weights' => [250, 500, 1000],
            ],

            // Tulang & Sup Gurih
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

            $product = Product::updateOrCreate(
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
