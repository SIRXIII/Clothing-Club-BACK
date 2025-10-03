<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Partner>
 */
class PartnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $randomDays = $this->faker->randomElements($days, 2);
        $daysString = implode(' - ', $randomDays);


        $startTime = $this->faker->numberBetween(8, 12) . 'am';
        $endTime = $this->faker->numberBetween(1, 11) . 'pm';
        $timeString = $startTime . ' - ' . $endTime;

        return [
            'profile_photo' => $this->faker->imageUrl(200, 200, 'business'),
            'name' => $this->faker->name(),
            'business_name' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'category' => $this->faker->randomElement(['Fashion', 'Electronics', 'Food', 'Health']),
            'location' => $this->faker->city(),
            'address' => $this->faker->address(),
            'store_available_days' => $daysString,
            'store_available_time' => $timeString,
            'tax_id' => $this->faker->bothify('??######'),
            'username' => $this->faker->unique()->userName(),
            'password' => Hash::make('password'),
            'status' => $this->faker->randomElement(['active', 'suspended', 'pending']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
