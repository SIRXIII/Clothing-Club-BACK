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

    public function getStates()
{
    try {
        $partnerId = auth()->user()->id;

        $states = [
            [
                'label' => 'Orders Today',
                'value' => '$' . Order::where('partner_id', $partnerId)
                    ->where('status', 'delivered')
                    ->whereDate('created_at', now())
                    ->sum('total_price'),
            ],
            [
                'label' => 'Items Out Now',
                'value' => Order::where('partner_id', $partnerId)
                    ->whereNotIn('status', ['returned', 'cancelled'])
                    ->count(),
            ],
            [
                'label' => 'Items Due Back Today',
                'value' => Order::where('partner_id', $partnerId)
                    ->where('status', 'returned')
                    ->whereDate('updated_at', now())
                    ->count(),
            ],

            // [
            //     'label' => 'Pending Payout',
            //     'value' => Order::where('partner_id', $partnerId)
            //         ->where('status', 'pending')
            //         ->count(),
            // ],
        ];

        return $this->success($states, 'Widget fetch');
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to fetch states: ' . $e->getMessage()
        ], 500);
    }
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
}
