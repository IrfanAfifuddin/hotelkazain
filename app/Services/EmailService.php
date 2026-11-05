<?php

namespace App\Services;

use App\Mail\PaymentConfirmationMail;
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

        $booking->loadMissing(['customer', 'rooms']);

        if ($booking->customer === null) {
            throw new LogicException('The confirmed booking does not have a customer.');
        }

        try {
            Mail::to($booking->customer->email)->send(new PaymentConfirmationMail($booking));

            $booking->forceFill(['booking_code_sent_at' => now()])->save();
        } catch (\Exception $e) {
            // Log error but don't fail the booking
            report($e);
        }
    }
}
