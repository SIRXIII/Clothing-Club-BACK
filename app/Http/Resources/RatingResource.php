<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
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
            'rider_name' => $this->rider->name,
            'order_id' => $this->order_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'traveler_id' => $this->traveler_id,
            'traveler_name' => $this->traveler ? $this->traveler->name : null,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
