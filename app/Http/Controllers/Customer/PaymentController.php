<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        return view('customer.payment', [
            'bookingCode' => $request->query('booking'),
            'midtransClientKey' => config('services.midtrans.client_key'),
            'midtransIsProduction' => config('services.midtrans.is_production'),
        ]);
    }

    public function createSnapTransaction(Booking $booking, PaymentService $paymentService): JsonResponse
    {
        $booking->load('customer');

        try {
            return response()->json([
                'data' => $paymentService->createPayment($booking),
            ]);
        } catch (RuntimeException $exception) {
            Log::warning('Midtrans payment request rejected.', [
                'booking_code' => $booking->booking_code,
                'exception' => $exception::class,
            ]);

            return response()->json(['message' => 'Transaksi pembayaran tidak dapat dibuat.'], 422);
        }
    }

    public function notification(Request $request, PaymentService $paymentService): JsonResponse
    {
        try {
            $paymentService->handleNotification();
        } catch (Throwable $exception) {
            Log::warning('Midtrans notification rejected.', [
                'exception' => $exception::class,
            ]);

            return response()->json(['message' => 'Invalid payment notification.'], 403);
        }

        return response()->json(['message' => 'Notification received.']);
    }

    public function status(Booking $booking): JsonResponse
    {
        return response()->json([
            'data' => [
                'booking_code' => $booking->booking_code,
                'booking_status' => $booking->booking_status,
                'payment_status' => $booking->payment_status,
                'payment_expires_at' => $booking->payment_expires_at?->toIso8601String(),
            ],
        ]);
    }
}
