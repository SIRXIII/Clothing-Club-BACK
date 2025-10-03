<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RefundResource;
use App\Models\Order;
use App\Models\Refund;
use App\Trait\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RefundController extends Controller
{
    use ApiResponse;
    public function index()
    {
        $partnerId = auth()->user()->id;

        try {
            $refunds = Refund::where('partner_id', $partnerId)->with(['order', 'traveler', 'partner', 'images'])->get();

            return $this->success(RefundResource::collection($refunds), 'Refunds retrieved successfully', 200);
        } catch (Exception $e) {
            return $this->error('Failed to retrieve refunds: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 422);
        }

        if (auth('traveler')->check()) {
            $sender = auth('traveler')->user();
        } elseif (auth('partner')->check()) {
            $sender = auth('partner')->user();
        } elseif (auth('rider')->check()) {
            $sender = auth('rider')->user();
        } else {
            $sender = auth()->user();
        }

        $order = Order::find($request->order_id);

        $refund = Refund::create([
            'order_id' => $order->id,
            'traveler_id' => $order->traveler_id,
            'partner_id' => $order->partner_id,
            'amount' => $order->total_price,
            'status' => "Pending",
            'reason' => $request->reason
        ]);

        return response()->json(['message' => 'Refund created successfully', 'refund' => $refund]);
    }

    public function show($id)
    {
        $refund = Refund::with(['order', 'traveler', 'partner', 'images', 'traveler.addresses'])->find($id);

        if (!$refund) {

            return $this->error('Refund not found', 404);
        }

        return $this->success(new RefundResource($refund), 'Refund retrieved successfully', 200);
    }


    public function updateStatus(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:refunds,id',
            'status' => 'in:Pending,Processed,Rejected',
        ]);

        $refund = Refund::find($data['id']);
        if (!$refund) {
            return $this->error('Refund not found', 404);
        }

        $refund->update($data);

        return $this->success(new RefundResource($refund), 'Refund updated successfully', 200);
    }

    public function isChatSupported()
    {

        return $this->success(['isChatSupported' => true], 'Chat support status retrieved', 200);
    }
}
