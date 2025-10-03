<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Traveler;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rental>
 */
class RentalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['active', 'completed', 'cancelled']);
        $start  = $this->faker->dateTimeBetween('-2 months', 'now');
        $end    = $status !== 'active' ? $this->faker->dateTimeBetween($start, '+1 month') : null;

        return [
            'product_id' => Product::inRandomOrder()->first()->id ?? Product::factory(),
            'traveler_id' => Traveler::inRandomOrder()->first()->id ?? Traveler::factory(),
            'start_date'  => $start,
            'end_date'    => $end,
            'status'      => $status,
            'price'       => $this->faker->randomFloat(2, 50, 500),
        ];
    }
}
