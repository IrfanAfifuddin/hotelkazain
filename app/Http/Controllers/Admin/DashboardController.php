<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Admin lengkap beserta data kamar tersedia untuk form pemesanan.
     */
    public function index(): View
    {
        $availableRooms = Room::where('room_status', 'Available')->orderBy('room_number')->get();
        $availableCount = $availableRooms->count();
        $occupiedCount = Room::where('room_status', 'Occupied')->count();
        $todayCheckInCount = Booking::whereDate('check_in_time', today())->count();
        $todayCheckOutCount = Booking::whereDate('check_out_time', today())->count();
        $recentBookings = Booking::with(['customer', 'room'])->latest()->take(5)->get();

        return view('dashboard', compact(
            'availableRooms',
            'availableCount',
            'occupiedCount',
            'todayCheckInCount',
            'todayCheckOutCount',
            'recentBookings'
        ));
    }
}

