<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('customer.home');
Route::redirect('/customer', '/')->name('customer.legacy-home');

// ROUTE AUTENTIKASI
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'processLogin']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ROUTE DASHBOARD — redirect berdasarkan role
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
});

// Route Admin
Route::prefix('admin')->group(function () {
    require __DIR__.'/admin.php';
});

// Route Resepsionis
Route::prefix('receptionist')->group(function () {
    require __DIR__.'/receptionist.php';
});

require __DIR__.'/customer.php';
