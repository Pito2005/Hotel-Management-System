<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    // Guest information
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'nationality',
        'address',
    ];

    // One guest can have many bookings
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
