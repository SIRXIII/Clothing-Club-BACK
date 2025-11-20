<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class OrderResource extends JsonResource
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
            'id'            => $this->id,
            'traveler_name' => $this->traveler->name,
            'traveler_photo' => $getHetznerUrl($this->traveler->profile_photo),
            'partner_name' => $this->partner?->name,
            'partner_photo' => $getHetznerUrl($this->partner->profile_photo),
            'items_count'   => $this->items->count(),
            'total_price'   => $this->total_price,
            'status'        => Str::ucfirst($this->status),
            'created_at'    => $this->created_at->format('F d, Y'),
            'dispatch_time' => $this->dispatch_time,
            'delivery_time' => $this->delivery_time,
            'rider_name'    => $this->rider?->name,
            'rider_photo'   => $getHetznerUrl($this->rider?->profile_photo),
            'complaints'    => $this->complaints?->count() ?? 0,
            'items'         => OrderItemResource::collection($this->whenLoaded('items')),
            'canceled_by'   => $this->when($this->status === 'cancelled', function () {
                return $this->canceledBy ? [
                    'type' => class_basename($this->canceledBy),
                    'name' => $this->canceledBy->name,
                ] : null;
            }),
            'partner'      => new PartnerResource($this->whenLoaded('partner')),
            'traveler'     => new TravelerResource($this->whenLoaded('traveler')),
            'rider'        => new RiderResource($this->whenLoaded('rider')),

        ];
    }
}
