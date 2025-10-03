<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\Traveler;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportMessage>
 */
class SupportMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
           'support_ticket_id' => SupportTicket::factory(),
            'senderable_id' => Traveler::factory(),
            'senderable_type' => Traveler::class,
            'message' => $this->faker->sentence(),
        ];
    }
}
