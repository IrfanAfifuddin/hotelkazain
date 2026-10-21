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
    public function index(Booking $booking): View
    {
        $booking->load(['customer', 'room']);

        return view('customer.payment', ['booking' => $booking]);
    }

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

    public function success(
        Request $request,
        PaymentService $paymentService,
        EmailService $emailService,
    ): View {
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
}
