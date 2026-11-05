<?php

namespace App\Models;

use Carbon\Carbon;
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

    protected $casts = [
        'room_status' => 'string',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the current computed room status based on bookings and time.
     * Returns: 'Available', 'Occupied', or 'Cleaning'
     */
    public function getComputedStatusAttribute(): string
    {
        $now = Carbon::now('Asia/Jakarta');

        // 1. Check for active booking
        $activeBooking = $this->getActiveBookingAttribute();
        if ($activeBooking) {
            return 'Occupied';
        }

        // 2. Check for recent checkout (automatic Cleaning)
        $recentBooking = $this->bookings()
            ->whereIn('booking_status', ['Confirmed', 'Check-in', 'Check-out'])
            ->where('check_out_time', '<=', $now)
            ->where('check_out_time', '>', $now->copy()->subHour())
            ->latest('check_out_time')
            ->first();

        if ($recentBooking) {
            return 'Cleaning';
        }

        // 3. Fallback to manual status if set
        if (in_array($this->room_status, ['Maintenance', 'Cleaning'])) {
            return $this->room_status;
        }

        return 'Available';
    }

    /**
     * Get the time when cleaning ends (if currently in Cleaning status).
     */
    public function getCleaningEndsAtAttribute(): ?Carbon
    {
        $now = Carbon::now('Asia/Jakarta');

        $recentBooking = $this->bookings()
            ->whereIn('booking_status', ['Confirmed', 'Check-in', 'Check-out'])
            ->where('check_out_time', '<=', $now)
            ->where('check_out_time', '>', $now->copy()->subHour())
            ->latest('check_out_time')
            ->first();

        if ($recentBooking) {
            return Carbon::parse($recentBooking->check_out_time, 'Asia/Jakarta')->addHour();
        }

        return null;
    }

    /**
     * Get the active booking currently occupying the room.
     */
    public function getActiveBookingAttribute(): ?Booking
    {
        $now = Carbon::now('Asia/Jakarta');

        return $this->bookings()
            ->whereIn('booking_status', ['Confirmed', 'Check-in'])
            ->where('check_in_time', '<=', $now)
            ->where('check_out_time', '>', $now)
            ->latest('check_in_time')
            ->first();
    }

    /**
     * Update the stored room_status based on computed status.
     * This should be called by the scheduler.
     */
    public function syncStatus(): void
    {
        $computedStatus = $this->computed_status;

        if ($this->room_status !== $computedStatus) {
            $this->update(['room_status' => $computedStatus]);
        }
    }
}
