<?php

namespace App\Services;

use App\Models\Booking;
use App\PaymentGatewayException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use UnexpectedValueException;

class PaymentService
{
    /**
     * @return array{order_id: string, token: string, redirect_url: string}
     */
    public function createPayment(Booking $booking): array
    {
        if ($booking->payment_method !== 'Virtual Account') {
            throw new PaymentGatewayException('Customer Midtrans payments are only available by bank transfer.');
        }

        // If both token and redirect_url already exist, reuse them
        if (
            $booking->midtrans_snap_token
            && $booking->midtrans_redirect_url
            && is_string($booking->midtrans_order_id)
            && $booking->midtrans_order_id !== ''
        ) {
            return [
                'order_id' => $booking->midtrans_order_id,
                'token' => $booking->midtrans_snap_token,
                'redirect_url' => $booking->midtrans_redirect_url,
            ];
        }

        $serverKey = $this->serverKey();
        $booking->loadMissing(['customer', 'room']);
        $orderId = $booking->midtrans_order_id ?: 'KZN-PAY-'.Str::uuid();

        if ($booking->midtrans_order_id !== $orderId) {
            $booking->forceFill(['midtrans_order_id' => $orderId])->save();
        }

        $expiry = $booking->payment_expires_at ?? now()->addMinutes(20);

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(15)
                ->post($this->snapBaseUrl().'/snap/v1/transactions', [
                    'transaction_details' => [
                        'order_id' => $orderId,
                        'gross_amount' => (int) $booking->total_price,
                    ],
                    'item_details' => [[
                        'id' => (string) $booking->room_id,
                        'price' => (int) $booking->total_price,
                        'quantity' => 1,
                        'name' => 'Kamar '.$booking->room->room_number.' Kazain Hotel',
                    ]],
                    'customer_details' => [
                        'first_name' => $booking->customer->customer_name,
                        'email' => $booking->customer->email,
                        'phone' => $booking->customer->phone_number,
                    ],
                    'enabled_payments' => ['bank_transfer'],
                    'expiry' => [
                        'start_time' => $booking->created_at->format('Y-m-d H:i:s O'),
                        'unit' => 'minute',
                        'duration' => max(1, (int) now()->diffInMinutes($expiry)),
                    ],
                    'callbacks' => [
                        'finish' => route('customer.success', ['order_id' => $orderId]),
                    ],
                ])
                ->throw();
        } catch (ConnectionException $exception) {
            throw new PaymentGatewayException(
                'Midtrans could not create a payment transaction.',
                transactionMayExist: true,
                previous: $exception,
            );
        } catch (RequestException $exception) {
            throw new PaymentGatewayException(
                'Midtrans could not create a payment transaction.',
                transactionMayExist: $exception->response->serverError(),
                previous: $exception,
            );
        }

        $token = $response->json('token');
        $redirectUrl = $response->json('redirect_url');

        if (! is_string($token) || $token === '' || ! is_string($redirectUrl) || $redirectUrl === '') {
            throw new PaymentGatewayException(
                'Midtrans returned an incomplete payment response.',
                transactionMayExist: true,
            );
        }

        $booking->forceFill([
            'midtrans_snap_token' => $token,
            'midtrans_redirect_url' => $redirectUrl,
        ])->save();

        return [
            'order_id' => $orderId,
            'token' => $token,
            'redirect_url' => $redirectUrl,
        ];
    }

    public function hasValidNotificationSignature(array $notification): bool
    {
        $orderId = $notification['order_id'] ?? null;
        $statusCode = $notification['status_code'] ?? null;
        $grossAmount = $notification['gross_amount'] ?? null;
        $signature = $notification['signature_key'] ?? null;

        if (! is_string($orderId) || ! is_string($statusCode) || ! is_string($grossAmount) || ! is_string($signature)) {
            return false;
        }

        $serverKey = $this->serverKey();

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expectedSignature, $signature);
    }

    public function applyNotification(array $notification): Booking
    {
        return $this->applyStatus($notification);
    }

    public function refreshStatus(Booking $booking): Booking
    {
        $serverKey = $this->serverKey();
        $orderId = $booking->midtrans_order_id;

        if (! is_string($orderId) || $orderId === '') {
            throw new UnexpectedValueException('The booking does not have a Midtrans order ID.');
        }

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->get($this->apiBaseUrl().'/v2/'.rawurlencode($orderId).'/status')
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new PaymentGatewayException(
                'Midtrans could not verify the payment status.',
                transactionMayExist: true,
                previous: $exception,
            );
        }

        $status = $response->json();

        if (! is_array($status) || ($status['order_id'] ?? null) !== $orderId) {
            throw new PaymentGatewayException('Midtrans returned an invalid payment status response.');
        }

        return $this->applyStatus($status);
    }

    private function applyStatus(array $status): Booking
    {
        $orderId = $status['order_id'] ?? null;

        if (! is_string($orderId) || $orderId === '') {
            throw new UnexpectedValueException('The Midtrans notification is missing its order ID.');
        }

        return DB::transaction(function () use ($orderId, $status): Booking {
            $booking = Booking::query()
                ->where('midtrans_order_id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            $grossAmount = $status['gross_amount'] ?? null;

            if (! is_string($grossAmount) && ! is_numeric($grossAmount)) {
                throw new UnexpectedValueException('The Midtrans notification is missing its gross amount.');
            }

            if (number_format((float) $grossAmount, 2, '.', '') !== number_format((float) $booking->total_price, 2, '.', '')) {
                throw new UnexpectedValueException('The Midtrans transaction amount does not match the booking.');
            }

            if ($booking->payment_status === 'Paid') {
                return $booking->load(['customer', 'room']);
            }

            $transactionStatus = $status['transaction_status'] ?? null;
            $fraudStatus = $status['fraud_status'] ?? null;
            $isPaid = $transactionStatus === 'settlement'
                || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

            if ($isPaid) {
                $booking->forceFill([
                    'payment_status' => 'Paid',
                    'booking_status' => 'Confirmed',
                    'payment_time' => isset($status['transaction_time'])
                        ? CarbonImmutable::parse($status['transaction_time'])
                        : now(),
                    'midtrans_transaction_id' => $status['transaction_id'] ?? null,
                    'midtrans_transaction_status' => $transactionStatus,
                    'midtrans_fraud_status' => $fraudStatus,
                ])->save();
            } elseif ($transactionStatus === 'expire') {
                $booking->forceFill([
                    'payment_status' => 'Expired',
                    'booking_status' => 'Expired',
                    'midtrans_transaction_id' => $status['transaction_id'] ?? null,
                    'midtrans_transaction_status' => $transactionStatus,
                    'midtrans_fraud_status' => $fraudStatus,
                ])->save();
            } elseif (in_array($transactionStatus, ['deny', 'cancel'], true)) {
                $booking->forceFill([
                    'payment_status' => 'Failed',
                    'booking_status' => 'Cancelled',
                    'midtrans_transaction_id' => $status['transaction_id'] ?? null,
                    'midtrans_transaction_status' => $transactionStatus,
                    'midtrans_fraud_status' => $fraudStatus,
                ])->save();
            }

            return $booking->fresh(['customer', 'room']);
        }, 3);
    }

    private function serverKey(): string
    {
        $serverKey = config('services.midtrans.server_key');

        if (! is_string($serverKey) || $serverKey === '') {
            throw new PaymentGatewayException('Midtrans is not configured.');
        }

        return $serverKey;
    }

    private function snapBaseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }

    private function apiBaseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }
}
