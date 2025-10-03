<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class Partner extends  Authenticatable
{
    use HasFactory, SoftDeletes, HasApiTokens;
    use TwoFactorAuthenticatable;

    protected $fillable = [

        'profile_photo',
        'name',
        'business_name',
        'email',
        'phone',
        'category',
        'location',
        'address',
        // 'documents',
        'username',
        'password',
        'status',
        'average_rating',
        'store_available_days',
        'store_available_time',
        'tax_id',
    ];

    protected $hidden = [
        'password',
        'provider_token',
        'provider_refresh_token',
        'two_factor_secret',
        'two_factor_email_code',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'two_factor_email_expires_at' => 'datetime',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'partner_id');
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ? round($this->ratings()->avg('rating'), 1) : null;
    }

    public function documents()
    {
        return $this->hasMany(PartnerDocument::class);
    }

    public function licenseDocuments()
    {
        return $this->documents()->where('type', 'license');
    }

    public function ownerIdCardDocuments()
    {
        return $this->documents()->where('type', 'owner_id_card');
    }

    public function getPendingOrdersCountAttribute()
    {
        return $this->orders()->where('status', 'pending')->count();
    }

    public function getCancelledOrdersCountAttribute()
    {
        return $this->orders()->where('status', 'cancelled')->count();
    }

    public function getDeliveredOrdersCountAttribute()
    {
        return $this->orders()->where('status', 'delivered')->count();
    }

    public function getTotalSalesAttribute()
    {
        return $this->orders()
            ->where('status', '!=', 'cancelled')
            ->sum('total_price');
    }

    public function complaints()
    {
        return $this->morphMany(Complaint::class, 'complainable');
    }


    public function getReviewsCountAttribute()
    {
        return $this->ratings()->count();
    }


    // In Partner.php, Traveler.php, Rider.php
    public function supportTickets()
    {
        return $this->morphMany(SupportTicket::class, 'user');
    }

     public function forceGenerateRecoveryCodes()
    {
        $codes = collect(range(1, 8))->map(fn() => \Illuminate\Support\Str::random(10))->all();

        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ])->save();

        return $codes;
    }
}
