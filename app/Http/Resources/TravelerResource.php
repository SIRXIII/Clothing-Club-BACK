<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class TravelerResource extends JsonResource
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
            'profile_photo' => $this->profile_photo,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => $this->country,
            'address' => $this->address,
            'spent_amount' => $this->spent_amount,
            // 'status' => $this->status,
            'status' => Str::ucfirst($this->status),

            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
            'created_at' => $this->created_at->format('M D, Y'),
            'updated_at' => $this->updated_at->toIso8601String(),
            'last_active' => $this->last_active?->diffForHumans(),
            'total_orders' => $this->orders_count,
            'total_amount_spent' =>  $this->total_amount_spent,
            'order' => OrderResource::collection($this->whenLoaded('orders')),
            'type' => "Traveler",

        ];
    }
}
