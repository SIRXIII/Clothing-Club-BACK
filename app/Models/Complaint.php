<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'message'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function complainable()
    {
        return $this->morphTo();
    }
}
