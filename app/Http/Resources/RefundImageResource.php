<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefundImageResource extends JsonResource
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
            'refund_id' => $this->refund_id,
            'image_path' => url($this->image_path),
            'created_at' => $this->created_at->format('F d, Y'),
        ];
    }
}
