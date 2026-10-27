<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    // Columns allowed to save
    protected $fillable = [
        'room_number',
        'room_type_id',
        'floor',
        'price_per_night',
        'status',
        'image',
        'description',
    ];

    // Each room belongs to a room type
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    // One room has many bookings
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
