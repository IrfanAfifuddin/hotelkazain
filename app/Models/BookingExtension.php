<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingExtension extends Model
{
    protected $fillable = [
        'booking_id',
        'extension_type',
        'duration_hours',
        'amount',
        'check_out_before',
        'check_out_after',
        'payment_method',
        'payment_status',
        'payment_time',
    ];

    protected $casts = [
        'check_out_before' => 'datetime',
        'check_out_after' => 'datetime',
        'payment_time' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
