<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = ['traveler_id', 'rating', 'comment'];

    public function rateable()
    {
        return $this->morphTo();
    }

    public function traveler()
    {
        return $this->belongsTo(Traveler::class);
    }
}
