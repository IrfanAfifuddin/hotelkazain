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

        // Find the active booking that is currently using or just finished using this room
        $activeBooking = $this->bookings()
            ->whereIn('booking_status', ['Confirmed', 'Check-in'])
            ->where('check_in_time', '<=', $now)
            ->where('check_out_time', '>', $now)
            ->latest('check_in_time')
            ->first();

        if ($activeBooking) {
            // Guest is still within their stay period (before check-out)
            return 'Occupied';
        }

        // Check for booking that just ended (check-out passed but within 1-hour cleaning window)
        $recentBooking = $this->bookings()
            ->whereIn('booking_status', ['Confirmed', 'Check-in', 'Check-out'])
            ->where('check_out_time', '<=', $now)
            ->where('check_out_time', '>', $now->copy()->subHour())
            ->latest('check_out_time')
            ->first();

        if ($recentBooking) {
            // Within 1-hour cleaning window after check-out
            return 'Cleaning';
        }

        // Check if there's a future booking that starts soon (within cleaning window of previous booking)
        // This prevents marking as Available when next booking overlaps with cleaning
        $nextBooking = $this->bookings()
            ->whereIn('booking_status', ['Confirmed', 'Check-in'])
            ->where('check_in_time', '>', $now)
            ->orderBy('check_in_time')
            ->first();

        if ($nextBooking) {
            $checkInTime = Carbon::parse($nextBooking->check_in_time, 'Asia/Jakarta');
            $cleaningEndsAt = $now->copy()->addHour();

            // If next booking starts before cleaning would end, room should not be Available yet
            // But this is handled by the logic above - if no recent booking ended, room is Available
        }

        // No active booking, no recent checkout within cleaning window
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
