<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // =====================================
    // FILLABLE FIELDS
    // =====================================

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'photo',
    ];

    // =====================================
    // HIDDEN FIELDS
    // =====================================

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // =====================================
    // ATTRIBUTE CASTS
    // =====================================

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // =====================================
    // CHECK ADMIN ROLE
    // =====================================

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // =====================================
    // CHECK STAFF ROLE
    // =====================================

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    // =====================================
    // USER BOOKINGS RELATIONSHIP
    // =====================================

    // One Admin or Staff can create many bookings.
    // Keep this function ONLY ONCE.

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id');
    }
}
