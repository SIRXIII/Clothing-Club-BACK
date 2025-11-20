<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    /**
     * Fetch all Orders.
     */

    public function index()
    {
        $partnerId = auth()->user()->id;
        try {
            $orders = Order::where('partner_id', $partnerId)
                ->with([
                    'items.product.primaryImage',
                    'items.product.images',
                    'partner',
                    'traveler',
                    'rider',
                    'complaints'
                ])
                ->get();
            return $this->success(OrderResource::collection($orders), 'Orders retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve orders: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Fetch a single Order by ID.
     */
    public function show($id)
    {
        try {
            $order = Order::with([
                'items.product.primaryImage',
                'items.product.images',
                'partner',
                'traveler',
                'rider',
                'complaints',
                'traveler.addresses'
            ])->find($id);

            if (!$order) {
                return $this->error('Partner not found', 404);
            }

            return $this->success(new OrderResource($order), 'order retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve order: ' . $e->getMessage(), 500);
        }
    }


    /**
     *  Assign a rider to an order.
     */

    public function assignRider(Request $request)
    {

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'rider_id' => 'required|exists:riders,id',
        ]);
        $order = Order::find($request->order_id);
        if (!$order) {
            return $this->error('Order not found', 404);
        }

        $order->rider_id = $request->rider_id;
        $order->status = 'processing';
        $order->save();

        // Reload with relationships
        $order->load([
            'items.product.primaryImage',
            'items.product.images',
            'partner',
            'traveler',
            'rider',
            'complaints'
        ]);

        return $this->success(new OrderResource($order), 'Rider assigned successfully', 200);
    }
}
