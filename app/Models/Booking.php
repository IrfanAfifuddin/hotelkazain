<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'room_id',
        'customer_id',
        'booking_code',
        'booking_type',
        'check_in_time',
        'check_out_time',
        'booking_status',
        'payment_method',
        'payment_status',
        'payment_time',
        'total_price',
        'source',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'payment_time' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(BookingExtension::class);
    }

    public static function generateCode(): string
    {
        do {
            $code = 'KZN-'.str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::query()->where('booking_code', $code)->exists());

        return $code;
    }
}
