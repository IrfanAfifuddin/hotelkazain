<?php

namespace App\Services;

use App\Mail\BookingConfirmationMail;
use App\Mail\PaymentConfirmationMail;
use App\Models\Booking;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use LogicException;
use Throwable;

class EmailService
{
    /**
     * Kirim email konfirmasi pemesanan untuk booking yang dibuat Admin/Resepsionis.
     *
     * Pengiriman dilakukan secara sinkron (bukan queue) agar email tetap terkirim
     * walaupun queue worker sedang tidak aktif.
     */
    public function sendBookingConfirmation(Booking $booking): void
    {
        $this->send($booking, new BookingConfirmationMail($booking));
    }

    /**
     * Kirim email kode booking setelah pembayaran customer dikonfirmasi.
     */
    public function sendBookingCode(Booking $booking): void
    {
        if ($booking->payment_status !== 'Paid' || $booking->booking_status !== 'Confirmed') {
            throw new LogicException('A booking code can only be emailed after a confirmed payment.');
        }

        if ($booking->booking_code_sent_at !== null) {
            return;
        }

        $booking->loadMissing('customer');

        if ($booking->customer === null) {
            throw new LogicException('The confirmed booking does not have a customer.');
        }

        $this->send($booking, new PaymentConfirmationMail($booking));
    }

    /**
     * Kirim mailable secara sinkron dan catat hasilnya ke log.
     */
    private function send(Booking $booking, Mailable $mailable): void
    {
        $booking->loadMissing(['customer', 'rooms']);

        if ($booking->customer === null || blank($booking->customer->email)) {
            return;
        }

        if ($booking->booking_code_sent_at !== null) {
            return;
        }

        try {
            Mail::to($booking->customer->email)->send($mailable);

            $booking->forceFill(['booking_code_sent_at' => now()])->save();

            Log::info('Email berhasil dikirim.', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'email' => $booking->customer->email,
                'mailable' => $mailable::class,
            ]);
        } catch (Throwable $e) {
            Log::error($e->getMessage(), [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'email' => $booking->customer->email,
                'mailable' => $mailable::class,
            ]);
        }
    }
}
