<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;
use RuntimeException;
use Throwable;

class PaymentService
{
    public function __construct()
    {
        Config::$serverKey = (string) config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /** @return array{token: string, redirect_url: string} */
    public function createPayment(Booking $booking): array
    {
        if ($booking->booking_status !== 'Pending'
            || $booking->payment_status !== 'Pending'
            || $booking->payment_expires_at?->isPast()) {
            throw ValidationException::withMessages([
                'booking' => ['Booking tidak lagi dapat dibayar.'],
            ]);
        }

        $booking->loadMissing('customer', 'room');

        if ($booking->total_price < 1 || ! $booking->customer || ! $booking->room) {
            throw new RuntimeException('Booking payment data is invalid.');
        }

        $orderId = $booking->midtrans_order_id ?: $booking->booking_code.'-'.bin2hex(random_bytes(8));

        if ($booking->midtrans_order_id && $booking->midtrans_snap_token) {
            return [
                'token' => $booking->midtrans_snap_token,
                'redirect_url' => '',
            ];
        }

        try {
            $transaction = Snap::createTransaction([
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $booking->total_price,
                ],
                'customer_details' => [
                    'first_name' => $booking->customer->customer_name,
                    'email' => $booking->customer->email,
                    'phone' => $booking->customer->phone_number,
                ],
                'item_details' => [[
                    'id' => $booking->booking_code,
                    'price' => (int) $booking->total_price,
                    'quantity' => 1,
                    'name' => 'Booking Hotel Kazain '.$booking->booking_code,
                ]],
                'callbacks' => [
                    'finish' => route('customer.success', ['booking' => $booking->booking_code]),
                    'pending' => route('customer.payment', ['booking' => $booking->booking_code]),
                    'error' => route('customer.payment', ['booking' => $booking->booking_code]),
                ],
            ]);
        } catch (Throwable $exception) {
            Log::warning('Midtrans Snap transaction creation failed.', [
                'booking_code' => $booking->booking_code,
                'exception' => $exception::class,
            ]);

            throw new RuntimeException('Tidak dapat membuat transaksi pembayaran saat ini.', previous: $exception);
        }

        $booking->forceFill([
            'midtrans_order_id' => $orderId,
            'midtrans_snap_token' => $transaction->token,
            'payment_method' => 'Midtrans',
        ])->save();

        return [
            'token' => $transaction->token,
            'redirect_url' => $transaction->redirect_url,
        ];
    }

    public function handleNotification(): void
    {
        $notification = new Notification;
        $orderId = (string) $notification->order_id;
        $statusCode = (string) $notification->status_code;
        $grossAmount = (string) $notification->gross_amount;
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.(string) config('services.midtrans.server_key'));

        if (! hash_equals($signature, (string) $notification->signature_key)) {
            throw new RuntimeException('Invalid Midtrans notification signature.');
        }

        DB::transaction(function () use ($notification, $orderId): void {
            $booking = Booking::query()
                ->where('midtrans_order_id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $notification->gross_amount !== (int) $booking->total_price) {
                throw new RuntimeException('Midtrans amount does not match booking total.');
            }

            $status = (string) $notification->transaction_status;
            $fraudStatus = (string) ($notification->fraud_status ?? '');
            $booking->midtrans_transaction_id = $notification->transaction_id;
            $booking->midtrans_transaction_status = $status;
            $booking->midtrans_fraud_status = $fraudStatus ?: null;

            if ($status === 'settlement' || ($status === 'capture' && $fraudStatus === 'accept')) {
                $booking->payment_status = 'Paid';
                $booking->payment_time ??= now();
                $booking->booking_status = 'Confirmed';
            } elseif (in_array($status, ['deny', 'cancel', 'expire'], true) && $booking->payment_status !== 'Paid') {
                $booking->payment_status = $status === 'deny' ? 'Failed' : 'Cancelled';
                $booking->booking_status = 'Cancelled';
            }

            $booking->save();
        }, 3);
    }
}
