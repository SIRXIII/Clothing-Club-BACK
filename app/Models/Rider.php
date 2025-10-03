<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    use HasFactory;

    protected $fillable = [
        'rider_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'availability_status',
        'status',
        'delivered_orders',
        'average_rating',
        'profile_photo',
        'license_front',
        'license_back',
        'license_plate',
        'vehicle_type',
        'vehicle_name',
        'assigned_region',
        'insurance_expire_date'

    ];

    protected $casts = [
        'insurance_expire_date' => 'date',
        'assigned_region' => 'array'
    ];

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }


    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }



    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function complaints()
    {
        return $this->morphMany(Complaint::class, 'complainable');
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

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ? round($this->ratings()->avg('rating'), 1) : null;
    }

    public function getAverageDeliveryTimeAttribute()
    {
        return $this->orders()
            ->where('status', 'delivered')
            ->whereNotNull('dispatch_time')
            ->whereNotNull('delivery_time')
            ->whereRaw('dispatch_time <= delivery_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, dispatch_time, delivery_time) / 60) as avg_time')
            ->value('avg_time');
    }

    public function getReviewsCountAttribute()
    {
        return $this->ratings()->count();
    }

    public function getInsuranceStatusAttribute(): string
    {
        if (!$this->insurance_expire_date) {
            return "No Insurance";
        }

        $expireDate = Carbon::parse($this->insurance_expire_date);

        if ($expireDate->isPast()) {
            return "Inactive (Expired: " . $expireDate->format('M D, Y') . ")";
        }

        return "Active (Expires: " . $expireDate->format('M Y') . ")";
    }

    public function getLicenseStatusAttribute(): string
    {
        if ($this->license_front && $this->license_back) {
            return "Uploaded";
        }
        return "Complete document is not uploaded";
    }


public function supportTickets()
{
    return $this->morphMany(SupportTicket::class, 'user');
}



}
