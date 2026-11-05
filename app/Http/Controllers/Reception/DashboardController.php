<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Receptionist lengkap beserta data kamar tersedia.
     */
    public function index(): View
    {
        $availableRooms = Room::where('room_status', 'Available')->orderBy('room_number')->get();
        $availableCount = $availableRooms->count();
        $occupiedCount = Room::where('room_status', 'Occupied')->count();
        $cleaningCount = Room::where('room_status', 'Cleaning')->count();
        $maintenanceCount = Room::where('room_status', 'Maintenance')->count();
        $todayCheckInCount = Booking::whereDate('check_in_time', today())->count();
        $todayCheckOutCount = Booking::whereDate('check_out_time', today())->count();
        $recentBookings = Booking::with(['customer', 'room'])
            ->whereNotIn('booking_status', ['Check-out', 'Cancelled'])
            ->where('check_out_time', '>', now())
            ->orderBy('check_in_time', 'desc')
            ->take(5)
            ->get();

        return view('receptionist.dashboard', compact(
            'availableRooms',
            'availableCount',
            'occupiedCount',
            'cleaningCount',
            'maintenanceCount',
            'todayCheckInCount',
            'todayCheckOutCount',
            'recentBookings'
        ));
    }
}
