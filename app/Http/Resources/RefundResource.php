<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class RefundResource extends JsonResource
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
            'order_id' => $this->order_id,
            'refund_id' => $this->refund_id,

            'reason' => $this->reason,
            'status' => Str::ucfirst($this->status),
            'requested_at' => $this->created_at->format('F d, Y'),
            'resolved_at' => $this->updated_at?->format('F d, Y'),
            'amount' => $this->amount,
            'comments' => $this->comments,
            'order' => new OrderResource($this->whenLoaded('order')),
            'traveler' => new TravelerResource($this->whenLoaded('traveler')),
            'partner' => new PartnerResource($this->whenLoaded('partner')),
            'images' => RefundImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
