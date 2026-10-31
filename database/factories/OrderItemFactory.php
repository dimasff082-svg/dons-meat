<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name_snapshot' => fake()->words(2, true),
            'product_sku_snapshot' => 'SKU-'.fake()->numerify('#####'),
            'weight_per_package_gram' => 500,
            'quantity' => 1,
            'total_weight_gram' => 500,
            'price_per_kg_snapshot' => 135000.00,
            'subtotal' => 67500.00,
        ];
    }
}
