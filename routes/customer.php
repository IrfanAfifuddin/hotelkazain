<?php

use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/availability', [BookingController::class, 'availability'])->name('customer.availability');
Route::post('/bookings', [BookingController::class, 'store'])->name('customer.bookings.store');
Route::get('/checkout', [BookingController::class, 'checkout'])->name('customer.checkout');
Route::get('/payment/{booking:midtrans_order_id}', [PaymentController::class, 'index'])->name('customer.payment');
Route::post('/payments/midtrans/notifications', [PaymentController::class, 'notification'])
    ->name('customer.payments.midtrans.notification');
Route::get('/success', [PaymentController::class, 'success'])->name('customer.success');
