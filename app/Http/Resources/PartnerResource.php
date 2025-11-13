<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartnerResource extends JsonResource
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
            'id' => $this->id,
            'profile_photo' => $getHetznerUrl($this->profile_photo),
            'business_name' => $this->business_name,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'category' => $this->category,
            'location' => $this->location,
            'address' => $this->address,
            'username' => $this->username,
            'store_available_days' => $this->store_available_days,
            'store_available_time' => $this->store_available_time,
            'tax_id' => $this->tax_id,
            'status' => Str::ucfirst($this->status),
            'total_sales' => $this->total_sales ?? 0,
            'delivered_orders_count' => $this->delivered_orders_count  ?? 0,
            'rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count ?? 0,
            'common_complaints' => ComplaintResource::collection($this->complaints()->distinct('message')->get()),
            'latest_complaint' => new ComplaintResource($this->complaints()->latest()->first()),
            'created_at' => $this->created_at->format('M D, Y'),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'products'  => ProductResource::collection($this->whenLoaded('products')),
            'order' => OrderResource::collection($this->whenLoaded('orders')),
            'type' => "Partner",
            'two_factor_secret' => !empty($this->two_factor_secret) ? true : false,
            'documents' => [
                'license' => $this->documents()->where('type', 'license')->get()->map(function ($doc) use ($getHetznerUrl) {
                    return [
                        'side' => $doc->side,
                        'file_path' => $getHetznerUrl($doc->file_path),
                    ];
                }),
                'owner_id_card' => $this->documents()->where('type', 'owner_id_card')->get()->map(function ($doc) use ($getHetznerUrl) {
                    return [
                        'side' => $doc->side,
                        'file_path' => $getHetznerUrl($doc->file_path),
                    ];
                }),
            ],
        ];
    }
}
