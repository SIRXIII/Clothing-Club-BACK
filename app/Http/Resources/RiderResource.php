<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RiderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rider_id' => $this->rider_id,
            'name' => $this->full_name,
            'first_name' => $this->first_name ?? 'N/A',
            'last_name' => $this->last_name ?? 'N/A',
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,

            // 'profile_photo' => $this->profile_photo ? url($this->profile_photo) : null,
            'profile_photo' => $this->profile_photo
                ? Storage::disk('hetzner')->url($this->profile_photo)
                : null,
            'license_status' => $this->license_status,
            'documents' => [
                'license_front' => $this->license_front ? Storage::disk('hetzner')->url($this->license_front) : null,
                'license_back' => $this->license_back ? Storage::disk('hetzner')->url($this->license_back) : null,
            ],

            'license_plate' => $this->license_plate,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_name' => $this->vehicle_name,
            'assigned_region' => $this->assigned_region,

            'insurance' => $this->insurance_status,
            'insurance_expire_date' => optional($this->insurance_expire_date)->format('Y-m-d'),


            'availability_status' => Str::ucfirst($this->availability_status),
            'status' => Str::ucfirst($this->status),

            'rating' => $this->average_rating ?? "0",
            'reviews_count' => $this->reviews_count ?? 0,
            'common_complaints' => ComplaintResource::collection($this->complaints()->distinct('message')->get()),
            'latest_complaint' => new ComplaintResource($this->complaints()->latest()->first()),


            'pending_orders_count' => $this->pending_orders_count ?? 0,
            'cancelled_orders_count' => $this->cancelled_orders_count ?? 0,
            'delivered_orders_count' => $this->delivered_orders_count ?? 0,
            'average_delivery_time' => $this->average_delivery_time ? round($this->average_delivery_time, 2) . ' minutes' : "-",

            'current_assigned_orders' => $this->pending_orders_count ?? 0,
            'order' => OrderResource::collection($this->whenLoaded('orders')),
            'type' => "Rider",


        ];
    }
}
