<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'room_id',
        'customer_id',
        'booking_code',
        'midtrans_order_id',
        'midtrans_snap_token',
        'midtrans_redirect_url',
        'booking_type',
        'check_in_time',
        'check_out_time',
        'booking_status',
        'payment_method',
        'payment_status',
        'payment_time',
        'total_price',
        'source',
        'payment_expires_at',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'payment_time' => 'datetime',
        'payment_expires_at' => 'datetime',
        'booking_code_sent_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeActiveOverlapping(Builder $query, CarbonInterface $start, CarbonInterface $end): void
    {
        $query
            ->where('check_in_time', '<', $end)
            ->where('check_out_time', '>', $start)
            ->where(function (Builder $query): void {
                $query
                    ->whereIn('booking_status', ['Confirmed', 'Check-in'])
                    ->orWhere(function (Builder $query): void {
                        $query
                            ->where('booking_status', 'Pending')
                            ->where(function (Builder $query): void {
                                $query
                                    ->whereNull('payment_expires_at')
                                    ->orWhere('payment_expires_at', '>', now());
                            });
                    });
            });
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
