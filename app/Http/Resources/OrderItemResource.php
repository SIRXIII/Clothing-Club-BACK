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
        // Get product image URL
        $productImageUrl = null;
        if ($this->product) {
            // Try primary image first
            $primaryImage = $this->product->primaryImage ?? $this->product->primaryImage()->first();
            if ($primaryImage && isset($primaryImage->image_path)) {
                $endpoint = env('HETZNER_S3_ENDPOINT', 'https://fsn1.your-objectstorage.com');
                $bucket = env('HETZNER_S3_BUCKET', 'tcc-media');
                $path = $primaryImage->image_path;
                
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    $productImageUrl = $path;
                } else {
                    $productImageUrl = rtrim($endpoint, '/') . '/' . $bucket . '/' . ltrim($path, '/');
                }
            } else {
                // Fallback to first image
                $firstImage = $this->product->images->first();
                if ($firstImage && isset($firstImage->image_path)) {
                    $endpoint = env('HETZNER_S3_ENDPOINT', 'https://fsn1.your-objectstorage.com');
                    $bucket = env('HETZNER_S3_BUCKET', 'tcc-media');
                    $path = $firstImage->image_path;
                    
                    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                        $productImageUrl = $path;
                    } else {
                        $productImageUrl = rtrim($endpoint, '/') . '/' . $bucket . '/' . ltrim($path, '/');
                    }
                }
            }
        }

        return [
            'id'          => $this->id,
            'product_id'  => $this->product_id,
            'product_name'=> $this->product->name,
            'product_image' => $productImageUrl,
            'quantity'    => $this->quantity,
            'price'       => $this->price,
            'total'       => $this->quantity * $this->price,

        ];
    }
}
