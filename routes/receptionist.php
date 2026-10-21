<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingExtensionController;
use App\Http\Controllers\Reception\DashboardController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

// Middleware untuk memastikan hanya Receptionist yang bisa akses
Route::middleware(['auth', 'role:Receptionist'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('receptionist.dashboard');

    // Manajemen Pemesanan
    Route::get('/pemesanan', [BookingController::class, 'index'])->name('receptionist.pemesanan.index');
    Route::get('/pemesanan/create', [BookingController::class, 'create'])->name('receptionist.pemesanan.create');
    Route::post('/pemesanan', [BookingController::class, 'store'])->name('receptionist.pemesanan.store');
    Route::get('/pemesanan/{booking}/edit', [BookingController::class, 'edit'])->name('receptionist.pemesanan.edit');
    Route::put('/pemesanan/{booking}', [BookingController::class, 'update'])->name('receptionist.pemesanan.update');
    Route::delete('/pemesanan/{booking}', [BookingController::class, 'destroy'])->name('receptionist.pemesanan.destroy');
    Route::get('/pemesanan/{booking}/extend', [BookingExtensionController::class, 'create'])->name('receptionist.pemesanan.extend.create');
    Route::post('/pemesanan/{booking}/extend', [BookingExtensionController::class, 'store'])->name('receptionist.pemesanan.extend.store');

    // Manajemen Status Kamar
    Route::get('/status-kamar', [RoomController::class, 'index'])->name('receptionist.status-kamar.index');
    Route::patch('/status-kamar/{room}', [RoomController::class, 'updateStatus'])->name('receptionist.status-kamar.update');
    Route::post('/status-kamar/{room}/extend', [RoomController::class, 'extend'])->name('receptionist.status-kamar.extend');
});
