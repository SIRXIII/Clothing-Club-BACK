<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'product_id' => $this->product_id,
            'image_url'  => url($this->image_path),
            'is_primary' => (bool) $this->is_primary,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
