<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
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
            'ticket_id'   => $this->ticket_id,
            'order_id'    => $this->order_id,
            'subject'     => $this->subject,
            'message'     => $this->message,
            'status'      => $this->status,
            'sender' => [
                'type'   => class_basename($this->user_type),
                'id'     => $this->user_id,
                'name'   => $this->user ? $this->user->name : null,
                'email'  => $this->user?->email ?? null,
                'phone'  => $this->user?->phone ?? null,
                'profile_photo' => $this->user?->profile_photo ?? null,
            ],

            'order'       => $this->whenLoaded('order', function () {
                return [
                    'id' => $this->order->id,
                    'status' => $this->order->status,
                ];
            }),

            'messages'    => SupportMessageResource::collection(
                $this->whenLoaded('messages')
            ),

            'created_at'  => $this->created_at->format('F d, Y'),
            'updated_at'  => $this->updated_at->format('F d, Y'),
        ];
    }
}
