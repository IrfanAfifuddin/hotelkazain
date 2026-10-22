<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerBookingRequest;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function availability(Request $request, AvailabilityService $availabilityService): JsonResponse|View
    {
        $validated = $request->validate([
            'date' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $availability = $availabilityService->checkAvailability(
            $validated['date'] ?? now()->toDateString(),
        );

        if ($request->expectsJson()) {
            return response()->json($availability);
        }

        return view('customer.availability', ['availability' => $availability]);
    }

    public function store(
        StoreCustomerBookingRequest $request,
        BookingService $bookingService,
    ): JsonResponse {
        $result = $bookingService->createBooking($request->validated());

        return response()->json([
            'message' => 'Booking created successfully.',
            'data' => [
                'customer' => [
                    'customer_name' => $result['customer']->customer_name,
                    'phone_number' => $result['customer']->phone_number,
                    'email' => $result['customer']->email,
                ],
                'booking' => $result['booking'],
            ],
        ], 201);
    }

    public function checkout(): View
    {
        return view('customer.checkout');
    }

    public function success(Request $request): View
    {
        return view('customer.success', [
            'bookingCode' => $request->query('booking'),
        ]);
    }
}
