<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerBookingRequest;
use App\PaymentGatewayException;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function availability(Request $request, AvailabilityService $availabilityService): JsonResponse|View
    {
        $validated = $request->validate([
            'date' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_in_time' => ['sometimes', 'date_format:H:i'],
            'booking_type' => ['sometimes', 'required_with:check_in_time', 'in:Short Time,Full Day'],
            'duration_hours' => ['sometimes', 'required_with:check_in_time', 'integer', 'in:5,8,24'],
        ]);

        $availability = $availabilityService->checkAvailability(
            $validated['date'] ?? now()->toDateString(),
            $validated['check_in_time'] ?? null,
            $validated['booking_type'] ?? null,
            isset($validated['duration_hours']) ? (int) $validated['duration_hours'] : null,
        );

        if ($request->expectsJson()) {
            return response()->json($availability);
        }

        return view('customer.availability', [
            'availability' => $availability,
            'roomTypes' => $availabilityService->roomTypes(),
        ]);
    }

    public function store(
        StoreCustomerBookingRequest $request,
        BookingService $bookingService,
        PaymentService $paymentService,
    ): JsonResponse|RedirectResponse {
        $result = $bookingService->createBooking($request->validated());

        try {
            $payment = $paymentService->createPayment($result['booking']);
        } catch (PaymentGatewayException $exception) {
            if (! $exception->transactionMayExist) {
                $bookingService->markPaymentSetupFailed($result['booking']);
            }

            report($exception);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pembayaran belum dapat dibuat. Silakan coba kembali.',
                ], 503);
            }

            return back()->withErrors([
                'payment_method' => 'Pembayaran belum dapat dibuat. Silakan coba kembali.',
            ]);
        }

        if (! $request->expectsJson()) {
            return redirect()->away($payment['redirect_url']);
        }

        return response()->json([
            'message' => 'Booking created successfully.',
            'data' => ['payment' => ['redirect_url' => $payment['redirect_url']]],
        ], 201);
    }

    public function checkout(AvailabilityService $availabilityService): View
    {
        return view('customer.checkout', ['roomTypes' => $availabilityService->roomTypes()]);
    }
}
