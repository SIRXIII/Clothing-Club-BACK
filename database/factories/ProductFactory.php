<?php
// database/factories/ProductFactory.php
namespace Database\Factories;

use App\Models\Product;
use App\Models\Rental;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name'              => $this->faker->word(),
            'brand'             => $this->faker->company(),
            'color'             => $this->faker->safeColorName(),

            'size'              => $this->faker->randomElement(['US 36R', 'US 38R', 'US 40R', 'US 42R']),
            'material'          => $this->faker->word(),
            'care_method'       => $this->faker->randomElement(['Dry Clean', 'Hand Wash', 'Machine Wash']),
            'weight'            => $this->faker->randomFloat(2, 0.1, 10) . ' kg',
            'sku'               => strtoupper($this->faker->bothify('SKU###??')),
             'type'              => $this->faker->randomElement(['rental', 'formal']),
             'category'          => $this->faker->randomElement(['suit', 'dress', 'accessory', 'shoes']),
            'base_price'        => $this->faker->randomFloat(2, 10, 500),
            'deposit'           => $this->faker->randomFloat(2, 5, 100),
            'late_fee'          => $this->faker->randomFloat(2, 5, 50),
            'replacement_value' => $this->faker->randomFloat(2, 100, 1000),
            'buy_price'         => $this->faker->randomFloat(2, 50, 600),
            'prep_buffer'       => $this->faker->numberBetween(1, 48),
            'min_rental'        => $this->faker->numberBetween(1, 5),
            'max_rental'        => $this->faker->numberBetween(5, 30),
            'blackout_date'     => $this->faker->optional()->date(),
            'fit_category'       =>  $this->faker->randomElement(['looser', 'tight', 'fit']),
            'location'          => $this->faker->city(),
            'length_unit'       => $this->faker->randomElement(['cm', 'inch']),
            'length'            => $this->faker->numberBetween(50, 200),
            'chest'             => $this->faker->numberBetween(30, 60),
            'sleeve'            => $this->faker->numberBetween(20, 70),
            'condition_grade'   => $this->faker->randomElement(['A', 'B', 'C']),
            'status'            => $this->faker->randomElement(['active', 'suspended', 'pending']),
            'note'              => $this->faker->sentence(),
            'stock'             => $this->faker->numberBetween(1, 100),
            'is_verified'       => $this->faker->boolean(70),
        ];
    }

     public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            if ($product->type === 'rental') {
                Rental::factory()
                    ->count(rand(2, 5))
                    ->create([
                        'product_id' => $product->id,
                    ]);
            }
        });
    }
}
