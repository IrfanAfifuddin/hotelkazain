<?php

use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('customer.home');
Route::get('/availability', [BookingController::class, 'availability'])->name('customer.availability');
Route::get('/checkout', [BookingController::class, 'checkout'])->name('customer.checkout');
Route::get('/success', [BookingController::class, 'success'])->name('customer.success');
Route::get('/payment', [PaymentController::class, 'index'])->name('customer.payment');
