<?php

namespace Database\Factories;

use App\Models\DeliveryZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryZone>
 */
class DeliveryZoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city().' Zone',
            'description' => fake()->sentence(),
            'fee' => fake()->randomElement([10000, 15000, 20000, 25000]),
            'minimum_order_amount' => null,
            'is_active' => true,
        ];
    }
}
