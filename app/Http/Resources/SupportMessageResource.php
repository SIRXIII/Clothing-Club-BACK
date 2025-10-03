<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportMessageResource extends JsonResource
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
            'message'     => $this->message,
            'attachment'  => $this->attachment ?? null,

            'sender_type' => class_basename($this->senderable_type),
            'sender_id'   => $this->senderable_id,
            'sender_name' => $this->senderable?->name ?? null,
            'sender_profile' => $this->senderable?->profile_photo ?? null,

            'created_at'  => $this->created_at->toIso8601String(),
            'updated_at'  => $this->updated_at->toIso8601String(),
           
        ];
    }
}
