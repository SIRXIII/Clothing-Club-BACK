<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
     use HasFactory;

    protected $fillable = [
        'refund_id',
        'order_id',
        'traveler_id',
        'partner_id',
        'amount',
        'status',
        'reason',
        // 'evidence_image',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function traveler()
    {
        return $this->belongsTo(Traveler::class, 'traveler_id');
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function images()
    {
        return $this->hasMany(RefundImage::class);
    }

    public function messages()
{
    return $this->hasMany(SupportTicket::class, 'refund_id');
}

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($refund) {
            $lastId = self::max('id') + 1;
            $refund->refund_id = 'REF-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
        });
    }
}
