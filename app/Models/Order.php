<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'partner_id',
        'traveler_id',
        'rider_id',
        'total_price',
        'status',
        'dispatch_time',
        'delivery_time',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function traveler()
    {
        return $this->belongsTo(Traveler::class, 'traveler_id');
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class, 'rider_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }


    public function canceledBy()
    {
        return $this->morphTo();
    }


    public function setStatusAttribute($value)
    {
        if ($value !== 'cancelled') {
            $this->attributes['canceled_by_id'] = null;
            $this->attributes['canceled_by_type'] = null;
        }
        $this->attributes['status'] = $value;
    }
}
