<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'product_id'  => $this->product_id,
            'product_name'=> $this->product->name,
            // 'product_price' => $this->product?->buy_price,
            'product_image' => $this->product?->primary_image,
            'quantity'    => $this->quantity,
            'price'       => $this->price,
            'total'       => $this->quantity * $this->price,

        ];
    }
}
