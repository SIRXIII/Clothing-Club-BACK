<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
            'base_price'        => $this->base_price,
            'deposit'           => $this->deposit,
            'late_fee'          => $this->late_fee,
            'replacement_value' => $this->replacement_value,
            'buy_price'         => $this->buy_price,
            'extensions'        => $this->extensions,
            'prep_buffer'       => $this->prep_buffer,
            'min_rental'        => $this->min_rental,
            'max_rental'        => $this->max_rental,
            'blackout_date'     => $this->blackout_date,
            'location'          => $this->location,
            'fit_category'      => $this->fit_category,
            'length_unit'       => $this->length_unit,
            'length'            => $this->length,
            'chest'             => $this->chest,
            'sleeve'            => $this->sleeve,
            'condition_grade'   => $this->condition_grade,
            'status'            => Str::ucfirst($this->status),
            'note'              => $this->note,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'primary_image'     => $this->primaryImage
                ? url($this->primaryImage->image_path)
                : ($this->images->first() ? url($this->images->first()->image_path) : null),
            'images'            => ProductImageResource::collection($this->whenLoaded('images')),
            'videos'           => ProductVideoResource::collection($this->whenLoaded('videos')),


            'rental_stats' => $this->type === 'rental' ? [
                'completed_rentals'      => $this->rentals()->where('status', 'completed')->count(),
                'cancelled_rentals'      => $this->rentals()->where('status', 'cancelled')->count(),
                'current_active_rentals' => $this->rentals()->where('status', 'active')->count(),
            ] : null,

            'verification_status' => $this->verification_status,



        ];
    }
}
