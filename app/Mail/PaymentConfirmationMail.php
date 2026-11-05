<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Konfirmasi Pembayaran — '.$this->booking->booking_code.' Kazain Hotel'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.payment-confirmation',
            text: 'mail.payment-confirmation-text'
        );
    }
}
