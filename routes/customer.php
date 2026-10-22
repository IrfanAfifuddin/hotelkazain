<?php

use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\PaymentController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('customer.home');
Route::get('/availability', [BookingController::class, 'availability'])->name('customer.availability');
Route::post('/bookings', [BookingController::class, 'store'])->name('customer.bookings.store');
Route::get('/checkout', [BookingController::class, 'checkout'])->name('customer.checkout');
Route::get('/success', [BookingController::class, 'success'])->name('customer.success');
Route::get('/payment', [PaymentController::class, 'index'])->name('customer.payment');
Route::post('/bookings/{booking:booking_code}/payment', [PaymentController::class, 'createSnapTransaction'])->name('customer.payment.snap');
Route::get('/bookings/{booking:booking_code}/payment-status', [PaymentController::class, 'status'])->name('customer.payment.status');
Route::post('/midtrans/notification', [PaymentController::class, 'notification'])->name('customer.payment.notification')->withoutMiddleware(VerifyCsrfToken::class);
