<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Rating;
use App\Models\Refund;
use App\Models\RefundImage;
use App\Models\Rider;
use App\Models\SupportTicket;
use App\Models\Traveler;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        User::create([
            'first_name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        $travelers = Traveler::factory(15)->create()
            ->each(function ($traveler) {
                Address::factory()->forTraveler($traveler)->create(['type' => 'shipping']);
                Address::factory()->forTraveler($traveler)->create(['type' => 'billing']);
            });

        $partners = Partner::factory(15)->create();
        $riders   = Rider::factory(15)->create();

        $partners->each(function ($partner) {
            Product::factory(rand(3, 7))->create([
                'partner_id' => $partner->id,
            ])->each(function ($product) {
                for ($i = 0; $i < 5; $i++) {
                    $product->images()->create([
                        'image_path' => fake()->imageUrl(200, 200, 'products'),
                        'is_primary' => $i === 0,
                        'sort_order' => $i,
                    ]);
                }

                if (rand(0, 1)) {
                    $product->videos()->create([
                        'video_url' => fake()->url(),
                        'thumbnail' => fake()->imageUrl(200, 200, 'products'),
                    ]);
                }
            });
        });

        $travelers->each(function ($traveler) use ($partners, $riders) {
            $orders = Order::factory(rand(3, 6))->create([
                'traveler_id' => $traveler->id,
                'partner_id'  => $partners->random()->id,
                'rider_id'    => $riders->random()->id,
                'status'      => Arr::random(['pending', 'delivered', 'cancelled']),
            ]);

            $orders->each(function ($order) use ($partners, $riders, $traveler) {
                $products = Product::where('partner_id', $order->partner_id)
                    ->inRandomOrder()
                    ->take(rand(1, 4))
                    ->get();

                $total = 0;

                foreach ($products as $product) {
                    $qty   = rand(1, 3);
                    $price = $product->base_price;
                    $lineTotal = $qty * $price;

                    OrderItem::factory()->create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'quantity'   => $qty,
                        'price'      => $price,
                        'total'      => $lineTotal,
                    ]);

                    $total += $lineTotal;
                }

                $order->update(['total_price' => $total]);

                if (rand(0, 1)) {
                    $complainable = Arr::random([
                        $traveler,
                        $riders->random(),
                        $partners->random(),
                    ]);

                    Complaint::factory()->create([
                        'order_id'          => $order->id,
                        'complainable_id'   => $complainable->id,
                        'complainable_type' => get_class($complainable),
                    ]);
                }


                if (rand(0, 1)) {
                    Rating::create([
                        'rating'      => rand(1, 5),
                        'comment'     => fake()->sentence(),
                        'traveler_id' => $order->traveler_id,
                        'rateable_id'   => $order->rider_id,
                        'rateable_type' => Rider::class,
                    ]);
                }
                if (rand(0, 1)) {
                    Rating::create([
                        'rating'      => rand(1, 5),
                        'comment'     => fake()->sentence(),
                        'traveler_id' => $order->traveler_id,
                        'rateable_id'   => $order->partner_id,
                        'rateable_type' => Partner::class,
                    ]);
                }
                if (rand(0, 1)) {
                    $product = $products->random();
                    Rating::create([
                        'rating'      => rand(1, 5),
                        'comment'     => fake()->sentence(),
                        'traveler_id' => $order->traveler_id,
                        'rateable_id'   => $product->id,
                        'rateable_type' => Product::class,
                    ]);
                }
            });

            $traveler->update(['last_active' => now()->subDays(rand(0, 30))]);


            Refund::factory()
                ->count(5)
                ->has(RefundImage::factory()->count(3), 'images')
                ->create();
        });

        SupportTicket::factory()
            ->count(5)
            ->create()
            ->each(function ($ticket) {
                $senders = [
                    Traveler::factory()->create(),
                    Partner::factory()->create(),
                    Rider::factory()->create(),
                    User::factory()->create(),
                ];

                foreach (range(1, 5) as $i) {
                    $sender = fake()->randomElement($senders);

                    $ticket->messages()->create([
                        'senderable_id'   => $sender->id,
                        'senderable_type' => get_class($sender),
                        'message'         => fake()->sentence(),
                    ]);
                }
            });
    }
}
