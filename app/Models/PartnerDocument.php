<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerDocument extends Model
{
     protected $fillable = [
        'partner_id',
        'type',
        'side',
        'file_path',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
