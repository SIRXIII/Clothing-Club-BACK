<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'order_id',
        'user_id',
        'user_type',
        'message',
        'subject',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

public function user()
{
    return $this->morphTo();
}


     public function messages()
    {
        return $this->hasMany(SupportMessage::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            $lastId = self::max('id') + 1;
            $ticket->ticket_id = 'TCK-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
        });
    }


}
