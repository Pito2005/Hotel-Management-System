<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelSetting extends Model
{
    // Fields allowed to be updated
    protected $fillable = [
        'hotel_name',
        'logo',
        'login_background',
        'updated_by',
    ];

    // Admin who last updated hotel branding
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
}
