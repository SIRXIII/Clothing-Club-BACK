<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductVideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Helper function to generate full URL with bucket name
        $getHetznerUrl = function($path) {
            if (!$path) return null;
            
            $endpoint = env('HETZNER_S3_ENDPOINT', 'https://fsn1.your-objectstorage.com');
            $bucket = env('HETZNER_S3_BUCKET', 'tcc-media');
            
            // Check if path already contains bucket name (already full URL)
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path; // Already a full URL
            }
            
            // Check if path already starts with bucket name
            if (str_starts_with($path, $bucket . '/')) {
                return "{$endpoint}/{$path}";
            }
            
            // Build full URL with bucket
            return "{$endpoint}/{$bucket}/{$path}";
        };

         return [
            'id'         => $this->id,
            'product_id' => $this->product_id,
            'video_path' => $getHetznerUrl($this->video_path),
            'video_url'  => $this->video_url,
            'thumbnail'  => $this->thumbnail ? $getHetznerUrl($this->thumbnail) : null,
        ];
    }
}
