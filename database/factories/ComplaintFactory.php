<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Partner;
use App\Models\Rider;
use App\Models\Traveler;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $complainableClass = $this->faker->randomElement([
            Traveler::class,
            Partner::class,
            Rider::class,
        ]);
          return [
            'order_id' => Order::inRandomOrder()->first()->id ?? Order::factory(),
            'complainable_id' => $complainableClass::inRandomOrder()->first()->id ?? $complainableClass::factory(),
            'complainable_type' => $complainableClass,
            'message' => $this->faker->randomElement([
                'Late delivery',
                'Limited size options',
                'Wrong product received',
                'Product damaged',
                'Customer not available',
                'Out of stock issue',
                'Rider was late',
            ]),
        ];
    }
}
