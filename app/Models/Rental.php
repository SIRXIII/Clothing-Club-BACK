<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
     use HasFactory;

    protected $fillable = [
        'product_id',
        'traveler_id',
        'start_date',
        'end_date',
        'status',
        'price',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function traveler()
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }
}
