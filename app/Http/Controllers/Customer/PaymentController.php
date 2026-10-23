<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\PaymentGatewayException;
use App\Services\EmailService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use UnexpectedValueException;

class PaymentController extends Controller
{
    /**
     * Legacy Snap flow: payment page accessed via ?booking=booking_code query param.
     * Expects $bookingCode in view for JavaScript to call createSnapTransaction.
     */
    public function index(Request $request): View
    {
        $bookingCode = $request->query('booking');

        return view('customer.payment', [
            'bookingCode' => $bookingCode,
            'midtransClientKey' => config('services.midtrans.client_key'),
            'midtransIsProduction' => config('services.midtrans.is_production'),
        ]);
    }

    /**
     * Redirect flow: payment page accessed via /payment/{midtrans_order_id} route binding.
     * Passes full $booking model to view for redirect_url approach.
     */
    public function indexRedirect(Booking $booking): View
    {
        $booking->load(['customer', 'room']);

        return view('customer.payment', ['booking' => $booking]);
    }

    /**
     * Legacy Snap flow: AJAX endpoint to create Snap transaction token.
     * Called by JavaScript in customer.payment view (legacy).
     */
    public function createSnapTransaction(Booking $booking, PaymentService $paymentService): JsonResponse
    {
        $booking->load('customer');

        try {
            return response()->json([
                'data' => $paymentService->createPayment($booking),
            ]);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => 'Transaksi pembayaran tidak dapat dibuat.'], 422);
        }
    }

    /**
     * Canonical Midtrans notification handler.
     * Verifies signature, applies status, sends email on paid.
     */
    public function notification(
        Request $request,
        PaymentService $paymentService,
        EmailService $emailService,
    ): JsonResponse {
        $notification = $request->all();

        try {
            $hasValidSignature = $paymentService->hasValidNotificationSignature($notification);
        } catch (PaymentGatewayException $exception) {
            report($exception);

            return response()->json(['message' => 'Midtrans is not configured.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if (! $hasValidSignature) {
            return response()->json(['message' => 'Invalid Midtrans signature.'], 403);
        }

        try {
            $booking = $paymentService->applyNotification($notification);
        } catch (UnexpectedValueException $exception) {
            report($exception);

            return response()->json(['message' => 'Invalid Midtrans notification.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($booking->payment_status === 'Paid') {
            $emailService->sendBookingCode($booking);
        }

        return response()->json(['message' => 'Notification processed.']);
    }

    /**
     * Legacy notification endpoint — delegates to canonical handler.
     */
    public function notificationLegacy(
        Request $request,
        PaymentService $paymentService,
        EmailService $emailService,
    ): JsonResponse {
        return $this->notification($request, $paymentService, $emailService);
    }

    /**
     * Payment status check for legacy Snap flow (polling).
     */
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

    /**
     * Success callback — supports both ?order_id= (redirect flow) and ?booking= (legacy).
     * For redirect flow: verifies status with Midtrans API, sends email if paid.
     * For legacy: shows status based on current DB state.
     */
    public function success(
        Request $request,
        PaymentService $paymentService,
        EmailService $emailService,
    ): View {
        // Redirect flow: ?order_id=midtrans_order_id
        if ($request->filled('order_id')) {
            $validated = $request->validate([
                'order_id' => ['required', 'string', 'max:64'],
            ]);

            $booking = Booking::query()
                ->where('midtrans_order_id', $validated['order_id'])
                ->with(['customer', 'room'])
                ->firstOrFail();

            $paymentStatusUnavailable = false;

            if (! in_array($booking->payment_status, ['Paid', 'Expired', 'Failed'], true)) {
                try {
                    $booking = $paymentService->refreshStatus($booking);
                } catch (PaymentGatewayException $exception) {
                    report($exception);
                    $paymentStatusUnavailable = true;
                }
            }

            if ($booking->payment_status === 'Paid') {
                $emailService->sendBookingCode($booking);
            }

            return view('customer.success', [
                'booking' => $booking,
                'paymentPending' => $booking->payment_status === 'Pending',
                'paymentStatusUnavailable' => $paymentStatusUnavailable,
            ]);
        }

        // Legacy Snap flow: ?booking=booking_code
        $bookingCode = $request->query('booking');

        $booking = $bookingCode
            ? Booking::query()
                ->where('booking_code', $bookingCode)
                ->with(['customer', 'room'])
                ->first()
            : null;

        $paymentPending = $booking && $booking->payment_status === 'Pending';

        return view('customer.success', [
            'booking' => $booking,
            'paymentPending' => $paymentPending,
            'paymentStatusUnavailable' => false,
        ]);
    }
}
