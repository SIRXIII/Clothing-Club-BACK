<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'image_url'  => $this->image_path ? Storage::disk('hetzner')->url($this->image_path) : null,
            'is_primary' => (bool) $this->is_primary,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
