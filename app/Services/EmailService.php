<?php

namespace App\Services;

use App\Mail\BookingCodeMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use LogicException;

class EmailService
{
    public function sendBookingCode(Booking $booking): void
    {
        if ($booking->payment_status !== 'Paid' || $booking->booking_status !== 'Confirmed') {
            throw new LogicException('A booking code can only be emailed after a confirmed payment.');
        }

        if ($booking->booking_code_sent_at !== null) {
            return;
        }

        $booking->loadMissing(['customer', 'room']);

        if ($booking->customer === null) {
            throw new LogicException('The confirmed booking does not have a customer.');
        }

        Mail::to($booking->customer->email)->send(new BookingCodeMail($booking));

        $booking->forceFill(['booking_code_sent_at' => now()])->save();
    }
}
