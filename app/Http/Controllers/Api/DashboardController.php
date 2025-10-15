<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Http\Resources\TravelerResource;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Rider;
use App\Models\Traveler;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    //     public function getStates()
    // {
    //     try {
    //         $partnerId = auth()->user()->id;

    //         $states = [
    //             [
    //                 'label' => 'Orders Today',
    //                 'value' => '$' . Order::where('partner_id', $partnerId)
    //                     ->where('status', 'delivered')
    //                     ->whereDate('created_at', now())
    //                     ->sum('total_price'),
    //             ],
    //             [
    //                 'label' => 'Items Out Now',
    //                 'value' => Order::where('partner_id', $partnerId)
    //                     ->whereNotIn('status', ['returned', 'cancelled'])
    //                     ->count(),
    //             ],
    //             [
    //                 'label' => 'Items Due Back Today',
    //                 'value' => Order::where('partner_id', $partnerId)
    //                     ->where('status', 'returned')
    //                     ->whereDate('updated_at', now())
    //                     ->count(),
    //             ],

    //             // [
    //             //     'label' => 'Pending Payout',
    //             //     'value' => Order::where('partner_id', $partnerId)
    //             //         ->where('status', 'pending')
    //             //         ->count(),
    //             // ],
    //         ];

    //         return $this->success($states, 'Widget fetch');
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Failed to fetch states: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function getStates()
    {
        try {
            $partnerId = auth()->user()->id;
            $today = now()->startOfDay();

            $ordersToday = Order::where('partner_id', $partnerId)
                ->where('status', 'delivered')
                ->whereDate('delivery_time', $today)
                ->sum('total_price');

            $itemsOutNow = Order::where('partner_id', $partnerId)
                ->whereNotNull('dispatch_time')
                ->whereNull('delivery_time')
                ->whereNotIn('status', ['returned', 'cancelled'])
                ->count();

            $dueBackToday = Order::with(['items.product'])
                ->where('partner_id', $partnerId)
                ->whereNotNull('dispatch_time')
                ->get()
                ->filter(function ($order) use ($today) {
                    $product = $order->items->first()?->product;
                    if (!$product || !$product->max_rental_period) return false;

                    $dueDate = \Carbon\Carbon::parse($order->dispatch_time)
                        ->addDays((int) $product->max_rental_period);

                    return $dueDate->isSameDay($today);
                })->count();


            $pendingPayout = Order::where('partner_id', $partnerId)
                ->where('status', 'pending_payout')
                ->sum('total_price');


            $ordersWithDispatch = Order::with(['items.product'])
                ->where('partner_id', $partnerId)
                ->whereNotNull('dispatch_time')
                ->get();

            $onTimeCount = 0;
            $totalOrders = $ordersWithDispatch->count();

            foreach ($ordersWithDispatch as $order) {
                $product = $order->items->first()?->product;
                if (!$product || !$product->prep_buffer) continue;

                $bufferDays = (int) $product->prep_buffer;
                $prepDeadline = \Carbon\Carbon::parse($order->created_at)
                    ->addDays($bufferDays);

                if (\Carbon\Carbon::parse($order->dispatch_time)->lessThanOrEqualTo($prepDeadline)) {
                    $onTimeCount++;
                }
            }

            $onTimePercentage = $totalOrders > 0
                ? round(($onTimeCount / $totalOrders) * 100, 1)
                : 0;

            $states = [
                [
                    'label' => 'Orders Today',
                    'value' => '$' . number_format($ordersToday, 2),
                ],
                [
                    'label' => 'Items Out Now',
                    'value' => $itemsOutNow,
                ],
                [
                    'label' => 'Items Due Back Today',
                    'value' => $dueBackToday,
                ],
                [
                    'label' => 'Pending Payout',
                    'value' => '$' . number_format($pendingPayout, 2),
                ],
                [
                    'label' => 'On-Time Prep Scoped',
                    'value' => $onTimePercentage . '%',
                ],
            ];

            return $this->success($states, 'Widget fetch successful');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch states: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function queue()
    {
        $partnerId = auth()->user()->id;
        $orders = Order::with(['items.product', 'partner', 'traveler'])
            ->where('partner_id', $partnerId)
            ->get()
            ->map(function ($order) {
                $product = optional($order->items->first())->product;
                return [
                    'id' => $order->id,
                    'product' => $product?->name,
                    'customer' => $order->traveler?->name ?? 'Unknown',
                    'status' => $order->status,
                    'product_availability' => $product?->product_availability ?? 'available',
                ];
            });

        return response()->json($orders);
    }





    public function travelersOverview()
    {

        try {
            $travelers = Traveler::with('orders')->latest()->take(5)->get();
            return $this->success(TravelerResource::collection($travelers), 'Travelers overview');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve travelers: ' . $e->getMessage(), 500);
        }
    }

    public function topPartners()
    {


        $topPartners = Partner::select('partners.*')
            ->selectSub(function ($query) {
                $query->from('orders')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('orders.partner_id', 'partners.id')
                    ->where('orders.status', 'delivered');
            }, 'delivered_orders_count')
            ->selectSub(function ($query) {
                $query->from('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->selectRaw('SUM(order_items.price * order_items.quantity)')
                    ->whereColumn('orders.partner_id', 'partners.id')
                    ->where('orders.status', 'delivered');
            }, 'total_sales')
            ->orderByDesc('total_sales')
            ->withAvg('ratings', 'rating')
            ->where('status', 'active')

            ->take(5)
            ->get();

        return $this->success(PartnerResource::collection($topPartners), 'Top Partners by sales');
    }


    public function latestAlert(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->take(3)
            ->get()
            ->values()
            ->map(function ($notification, $index) {

                switch ($index) {
                    case 0:
                        $color = 'text-red-500';
                        $statusColor = 'bg-[#E1FDFD] text-[#3E77B0]';
                        $descriptionColor = 'text-[#ED6C3C]';
                        break;
                    case 1:
                        $color = 'text-yellow-700';
                        $statusColor = 'bg-[#FEFCDD] text-[#8F802E]';
                        $descriptionColor = 'text-[#8F802E]';
                        break;
                    case 2:
                        $color = 'text-green-700';
                        $statusColor = 'bg-[#E7F7ED] text-[#088B3A]';
                        $descriptionColor = 'text-[#8F802E]';
                        break;
                    default:
                        $color = 'text-gray-500';
                        $statusColor = 'bg-gray-100 text-gray-800';
                        $descriptionColor = 'text-gray-600';
                        break;
                }

                $data = $notification->data;

                return [
                    'id' => $notification->id,
                    'label' => ucfirst($data['type']) ?? 'System Alert',
                    'value' => $data['title'] ?? '',
                    'description' => $data['message'] ?? '',
                    'link' => $data['url'] ?? '',
                    'date' => $notification->created_at
                        ? $notification->created_at->format('M d, Y - h:i A')
                        : null,
                    'color' => $color,
                    'statusColor' => $statusColor,
                    'descriptionColor' => $descriptionColor,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Latest alerts',
            'data' => $notifications,
        ]);
    }
}
