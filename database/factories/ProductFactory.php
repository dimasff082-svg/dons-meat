<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = 'Daging '.fake()->word().' '.Str::random(4);

        return [
            'category_id' => Category::factory(),
            'sku' => 'DM-'.strtoupper(Str::random(6)),
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'image_path' => null,
            'price_per_kg' => fake()->numberBetween(100, 250) * 1000,
            'stock_gram' => 20000,
            'reserved_stock_gram' => 0,
            'low_stock_threshold_gram' => 3000,
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => [
            'stock_gram' => 0,
            'reserved_stock_gram' => 0,
        ]);
    }
}
