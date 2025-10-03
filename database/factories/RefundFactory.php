<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Partner;
use App\Models\Traveler;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Refund>
 */
class RefundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         static $counter = 1;

         $order = Order::inRandomOrder()->first() ?? Order::factory()->create();

        return [
            'refund_id'   => 'REF-' . str_pad($counter++, 4, '0', STR_PAD_LEFT),
            'order_id'    => $order->id,
            'traveler_id' => Traveler::inRandomOrder()->first()->id ?? Traveler::factory(),
            'partner_id'  => Partner::inRandomOrder()->first()->id ?? Partner::factory(),
            'status'      => $this->faker->randomElement(['Pending', 'Processed', 'Rejected']),
            'reason'      => $this->faker->optional()->sentence(),
            'amount'      => $order->total_price, 
        ];
    }
}
