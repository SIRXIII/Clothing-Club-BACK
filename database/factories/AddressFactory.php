<?php

namespace Database\Factories;

use App\Models\Traveler;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'addressable_id' => null,
            'addressable_type' => null,
            'type' => $this->faker->randomElement(['shipping', 'billing']),
            'name' => $this->faker->name(),
            'address' => $this->faker->streetAddress() . ', ' . $this->faker->city() . ', ' . $this->faker->stateAbbr() . ' ' . $this->faker->postcode(),
            'country' => $this->faker->country(),
            'phone' => $this->faker->phoneNumber(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

     public function forTraveler($traveler)
    {
        return $this->state([
            'addressable_id' => $traveler->id,
            'addressable_type' => Traveler::class,
        ]);
    }
}
