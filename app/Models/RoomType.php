<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    // Columns allowed to save
    protected $fillable = [
        'name',
        'description',
        'base_price',
    ];

    // One room type has many rooms
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
