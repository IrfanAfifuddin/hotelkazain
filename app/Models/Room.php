<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'room_number',
        'room_type',
        'room_status',
        'price_5_hours',
        'price_8_hours',
        'full_day_price',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
