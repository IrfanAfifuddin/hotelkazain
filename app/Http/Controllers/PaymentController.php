<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(Booking $booking): View
    {
        abort_unless(in_array(auth()->user()?->role, ['Receptionist', 'Admin'], true), 403);

        return view('bookings.payment', [
            'booking' => $booking->load('customer', 'room'),
            'paymentCreateRoute' => auth()->user()?->role === 'Admin'
                ? route('admin.pemesanan.payment.create', $booking)
                : route('receptionist.pemesanan.payment.create', $booking),
            'midtransClientKey' => config('services.midtrans.client_key'),
            'midtransIsProduction' => config('services.midtrans.is_production'),
        ]);
    }

    public function createSnapTransaction(Booking $booking, PaymentService $paymentService): View
    {
        abort_unless(in_array(auth()->user()?->role, ['Receptionist', 'Admin'], true), 403);

        $transaction = $paymentService->createPayment($booking);

        return view('bookings.payment', [
            'booking' => $booking->fresh()->load('customer', 'room'),
            'paymentCreateRoute' => auth()->user()?->role === 'Admin'
                ? route('admin.pemesanan.payment.create', $booking)
                : route('receptionist.pemesanan.payment.create', $booking),
            'snapToken' => $transaction['token'],
            'midtransClientKey' => config('services.midtrans.client_key'),
            'midtransIsProduction' => config('services.midtrans.is_production'),
        ]);
    }
}
