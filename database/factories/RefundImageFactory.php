<?php

namespace Database\Factories;

use App\Models\Refund;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RefundImage>
 */
class RefundImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'refund_id'  => Refund::inRandomOrder()->first()->id ?? Refund::factory(),
            'image_path' => 'refunds/evidence/' . $this->faker->uuid . '.jpg',
        ];
    }
}
