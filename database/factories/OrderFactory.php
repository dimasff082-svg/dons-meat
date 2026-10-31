<?php

namespace Database\Factories;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_code' => 'ORD-'.strtoupper(Str::random(10)),
            'public_tracking_token' => Str::random(40),
            'channel' => OrderChannel::Online,
            'customer_name' => fake()->name(),
            'customer_phone' => '0812'.fake()->numerify('########'),
            'customer_email' => fake()->safeEmail(),
            'fulfillment_method' => FulfillmentMethod::Pickup,
            'delivery_zone_id' => null,
            'delivery_address' => null,
            'subtotal' => 100000.00,
            'delivery_fee' => 0.00,
            'total_amount' => 100000.00,
            'order_status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Unpaid,
            'payment_deadline_at' => now()->addHours(2),
        ];
    }
}
