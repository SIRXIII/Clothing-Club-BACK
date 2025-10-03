<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
      protected $fillable = [
        'support_ticket_id',
        'senderable_id',
        'senderable_type',
        'message',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function senderable()
    {
        return $this->morphTo();
    }   
}
