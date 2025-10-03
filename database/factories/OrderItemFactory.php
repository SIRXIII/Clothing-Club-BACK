<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
     protected $model = OrderItem::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
    $qty = $this->faker->numberBetween(1, 5);
    $price = $product->base_price ?? 100;

    return [
        'order_id'   => Order::inRandomOrder()->first()->id ?? Order::factory(),
        'product_id' => $product->id,
        'quantity'   => $qty,
        'price'      => $price,
        'total'      => $qty * $price,
    ];
    }
}
