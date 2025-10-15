<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function videos()
    {
        return $this->hasMany(ProductVideo::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'product_id');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true)->orderBy('sort_order');
    }

    public function activeRentals()
    {
        return $this->rentals()->where('status', 'active');
    }

    public function completedRentals()
    {
        return $this->rentals()->where('status', 'completed');
    }

    public function cancelledRentals()
    {
        return $this->rentals()->where('status', 'cancelled');
    }
    public function getVerificationStatusAttribute()
    {
        return $this->is_verified ? 'Verified' : 'Pending Verification';
    }


    //    public function getReviewsCountAttribute()
    // {
    //     return $this->ratings()->count();
    // }


    //     public function getAverageRatingAttribute()
    // {
    //     return $this->ratings()->avg('rating') ? round($this->ratings()->avg('rating'), 1) : null;
    // }
}
