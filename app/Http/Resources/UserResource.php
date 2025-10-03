<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->full_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'type' => $this->type ?? "admin",
            'two_factor_method' => $this->two_factor_method,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // 'profile_photo' => $this->profile_photo ? url($this->profile_photo) : null,
             'profile_photo' => $this->profile_photo
                ? Storage::disk('hetzner')->url($this->profile_photo)
                : null,
            'two_factor_secret' => !empty($this->two_factor_secret) ? true : false,
        ];
    }
}
