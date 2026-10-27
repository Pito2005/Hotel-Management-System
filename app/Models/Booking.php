<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    // Booking information
    protected $fillable = [
        'room_id',
        'guest_id',
        'user_id',
        'check_in',
        'check_out',
        'nights',
        'adults',
        'price_per_night',
        'total_amount',
        'status',
        'payment_status',
        'paid_at',
        'notes',
    ];

    // Convert database values
    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'paid_at' => 'datetime',
            'price_per_night' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    // Booking belongs to a room
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    // Booking belongs to a guest
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    // Booking was created by Admin or Staff
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
