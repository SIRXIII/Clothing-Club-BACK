<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rider>
 */
class RiderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition(): array
{
    $vehicleType = $this->faker->randomElement(['bike', 'car', 'scooter', 'van']);
     $vehicleNames = [
        'bike' => [
            'Honda CG125',
            'Yamaha YBR125',
            'Suzuki GS150',
            'Kawasaki Ninja 250',
            'Yamaha R1',
        ],
        'car' => [
            'Toyota Corolla',
            'Honda Civic',
            'Suzuki Alto',
            'Hyundai Tucson',
            'Kia Sportage',
        ],
        'scooter' => [
            'Honda Activa',
            'Suzuki Access',
            'Yamaha Ray ZR',
            'Vespa Primavera',
        ],
        'van' => [
            'Toyota Hiace',
            'Suzuki Bolan',
            'Hyundai H1',
            'Ford Transit',
        ],
    ];
    return [
        'rider_id' => 'RID-' . $this->faker->unique()->numberBetween(1000, 9999),
        'first_name' => $this->faker->firstName(),
        'last_name' => $this->faker->lastName(),
        'phone' => $this->faker->phoneNumber(),
        'email' => $this->faker->unique()->safeEmail(),
        'address' => $this->faker->address(),
        'availability_status' => $this->faker->randomElement(['online', 'offline']),
        'status' => $this->faker->randomElement(['active', 'suspended']),
        'delivered_orders' => $this->faker->numberBetween(0, 100),
        'average_rating' => $this->faker->randomFloat(2, 0, 5),
        'profile_photo' => $this->faker->imageUrl(200, 200, 'people'),
        'license_front' => $this->faker->imageUrl(400, 300, 'documents'),
        'license_back' => $this->faker->imageUrl(400, 300, 'documents'),
        'license_plate' => strtoupper($this->faker->bothify('??-####')),
        'vehicle_type' => $vehicleType,
        'vehicle_name' => $this->faker->randomElement($vehicleNames[$vehicleType]),
        'assigned_region' => $this->faker->city(),
         'insurance_expire_date' => $this->faker->date('Y-m-d', '+2 years'),
    ];
}

}
