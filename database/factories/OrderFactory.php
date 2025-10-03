<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Partner;
use App\Models\Rider;
use App\Models\Traveler;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{

    protected $model = Order::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $riderId = $this->faker->boolean(60)
            ? (Rider::inRandomOrder()->first()->id ?? Rider::factory())
            : null;

        return [
            'partner_id' => Partner::inRandomOrder()->first()->id ?? Partner::factory(),
            'traveler_id' => Traveler::inRandomOrder()->first()->id ?? Traveler::factory(),
            'rider_id' => $riderId,

            'total_price' => $this->faker->randomFloat(2, 50, 5000),
            'status' => $riderId
                ? $this->faker->randomElement(['pending', 'approved', 'processing', 'shipped', 'delivered', 'cancelled', 'returned', 'refunded'])
                : 'pending',
            'dispatch_time' => $this->faker->dateTimeBetween('-2 days', 'now'),
            'delivery_time' => $this->faker->optional()->dateTimeBetween('now', '+2 days'),


            'canceled_by_id' => function (array $attributes) {
                return $attributes['status'] === 'cancelled'
                    ? $this->faker->randomElement([
                        Partner::factory()->create()->id,
                        Traveler::factory()->create()->id,
                        Rider::factory()->create()->id,
                    ])
                    : null;
            },
            'canceled_by_type' => function (array $attributes) {
                if ($attributes['status'] !== 'cancelled') {
                    return null;
                }

                return $this->faker->randomElement([
                    Partner::class,
                    Traveler::class,
                    Rider::class,
                ]);
            },
        ];
    }
}
