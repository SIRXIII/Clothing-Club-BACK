<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVideoResource extends JsonResource
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
            'video_path' => $this->video_path ? url($this->video_path) : null,
            'video_url'  => $this->video_url,
            'thumbnail'  => $this->thumbnail ? url($this->thumbnail) : null,
        ];
    }
}
