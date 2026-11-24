<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10, 150) * 1000;
        $shipping = $subtotal >= 50000 ? 0 : 3000;

        return [
            'status' => OrderStatus::Pending,
            'customer_name' => fake()->name(),
            'phone' => '+9627'.fake()->numerify('########'),
            'email' => fake()->optional()->safeEmail(),
            'city' => fake()->randomElement(config('store.cities')),
            'address' => fake()->streetAddress(),
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'locale' => 'en',
        ];
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(['status' => $status]);
    }
}
