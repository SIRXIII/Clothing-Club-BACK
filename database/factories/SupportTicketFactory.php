<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Partner;
use App\Models\Refund;
use App\Models\Rider;
use App\Models\Traveler;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $userTypes = [
            Traveler::class,
            Partner::class,
            Rider::class,
            User::class,
        ];

        $userTypeClass = fake()->randomElement($userTypes);
        $userType = $userTypeClass::factory()->create();

        return [
            'order_id'      => Order::factory(),
            'user_id'   => $userType->id,
            'user_type' => $userTypeClass,
            'subject'       => fake()->sentence(),
            'message'       => fake()->paragraph(),
            'status'        => fake()->randomElement(['Pending', 'In Progress', 'Resolved']),
        ];
    }
}
