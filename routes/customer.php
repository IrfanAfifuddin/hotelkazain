<?php

use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\PaymentController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/availability', [BookingController::class, 'availability'])->name('customer.availability');
Route::post('/bookings', [BookingController::class, 'store'])->name('customer.bookings.store');
Route::get('/checkout', [BookingController::class, 'checkout'])->name('customer.checkout');

// Success page — requires the private Midtrans order ID.
Route::get('/success', [PaymentController::class, 'success'])->name('customer.success');

// Payment page — redirect flow (midtrans_order_id route binding)
Route::get('/payment/{booking:midtrans_order_id}', [PaymentController::class, 'indexRedirect'])->name('customer.payment.redirect');

// Canonical Midtrans notification endpoint (used by Midtrans dashboard config)
Route::post('/payments/midtrans/notifications', [PaymentController::class, 'notification'])
    ->name('customer.payments.midtrans.notification')
    ->withoutMiddleware(VerifyCsrfToken::class);
