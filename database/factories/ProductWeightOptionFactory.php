<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductWeightOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductWeightOption>
 */
class ProductWeightOptionFactory extends Factory
{
    protected $model = ProductWeightOption::class;

    public function definition(): array
    {
        $weightGram = fake()->randomElement([250, 500, 1000, 2000]);
        $label = $weightGram >= 1000
            ? ($weightGram / 1000).' kg'
            : $weightGram.' g';

        return [
            'product_id' => Product::factory(),
            'weight_gram' => $weightGram,
            'label' => $label,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
