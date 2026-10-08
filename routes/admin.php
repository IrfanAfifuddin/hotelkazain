<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

// Middleware untuk memastikan hanya Admin yang bisa akses
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Manajemen Pemesanan (Menggunakan prefix 'admin' agar unik)
    Route::get('/pemesanan', [BookingController::class, 'index'])->name('admin.pemesanan.index');
    Route::get('/pemesanan/create', [BookingController::class, 'create'])->name('admin.pemesanan.create');
    Route::post('/pemesanan', [BookingController::class, 'store'])->name('admin.pemesanan.store');
    Route::get('/pemesanan/{booking}/edit', [BookingController::class, 'edit'])->name('admin.pemesanan.edit');
    Route::put('/pemesanan/{booking}', [BookingController::class, 'update'])->name('admin.pemesanan.update');
    Route::delete('/pemesanan/{booking}', [BookingController::class, 'destroy'])->name('admin.pemesanan.destroy');

    // Manajemen Status Kamar
    Route::get('/status-kamar', [RoomController::class, 'index'])->name('admin.status-kamar.index');
    Route::patch('/status-kamar/{room}', [RoomController::class, 'updateStatus'])->name('admin.status-kamar.update');
    Route::post('/status-kamar/{room}/extend', [RoomController::class, 'extend'])->name('admin.status-kamar.extend');

    // Laporan & Akun
    Route::get('/laporan-penjualan', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/kelola-akun', [UserController::class, 'index'])->name('admin.users.index');
});
