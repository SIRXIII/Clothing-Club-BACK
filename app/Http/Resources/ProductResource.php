<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductResource extends JsonResource
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
            'id'                => $this->id,
            'product_id'        => "PRD-" . $this->id,
            'partner'           => new PartnerResource($this->whenLoaded('partner')),
            'name'              => $this->name,
            'brand'             => $this->brand,
            'color'             => $this->color,
            'size'             => $this->size,
            'type'             => Str::ucfirst($this->type),
            'category'             => $this->category,
            'material'          => $this->material,
            'care_method'       => $this->care_method,
            'weight'            => $this->weight,
            'sku'               => $this->sku,
            'stock'               => $this->stock,
            'barcode'               => $this->barcode,
            'base_price'        => $this->base_price,
            'deposit'           => $this->deposit,
            'late_fee'          => $this->late_fee,
            'replacement_value' => $this->replacement_value,
            'buy_price'         => $this->buy_price,
            'extensions_price'        => $this->extensions_price,
            'prep_buffer'       => $this->prep_buffer,
            'min_rental_period'        => $this->min_rental_period,
            'max_rental_period'        => $this->max_rental_period,
            'keep_to_buy_price'        => $this->keep_to_buy_price,
            'blackout_date'     => $this->blackout_date,
            'location'          => $this->location,
            'fit_category'      => $this->fit_category,
            'unit'       => $this->unit,
            'length'            => $this->length,
            'chest'             => $this->chest,
            'sleeve'            => $this->sleeve,
            'condition_grade'   => $this->condition_grade,
            'product_availibity'   => $this->product_availibity,
            'status'            => Str::ucfirst($this->status),
            'note'              => $this->note,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'primary_image'     => $this->primaryImage
                ? $getHetznerUrl($this->primaryImage->image_path)
                : ($this->images->first() ? $getHetznerUrl($this->images->first()->image_path) : null),
            'images'            => ProductImageResource::collection($this->whenLoaded('images')),
            'videos'           => ProductVideoResource::collection($this->whenLoaded('videos')),
            'sizes'            => $this->whenLoaded('sizes', function () {
                return $this->sizes->map(function ($size) {
                    return [
                        'id' => $size->id,
                        'size' => $size->size,
                        'quantity' => $size->quantity,
                    ];
                });
            }) ?? ($this->relationLoaded('sizes') ? [] : null),


            'rental_stats' => $this->type === 'rental' ? [
                'completed_rentals'      => $this->rentals()->where('status', 'completed')->count(),
                'cancelled_rentals'      => $this->rentals()->where('status', 'cancelled')->count(),
                'current_active_rentals' => $this->rentals()->where('status', 'active')->count(),
            ] : null,

            'verification_status' => $this->verification_status,
            'average_rating' => round($this->ratings->avg('rating'), 1),
                'ratings_count' => $this->ratings->count(),



        ];
    }
}
