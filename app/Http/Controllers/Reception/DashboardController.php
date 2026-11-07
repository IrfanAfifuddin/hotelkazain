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
        $rooms = Room::with(['bookings' => function ($q) {
            $q->whereIn('booking_status', ['Confirmed', 'Check-in', 'Check-out'])
                ->orderBy('check_in_time', 'desc');
        }])->get();

        $availableRooms = $rooms->filter(fn ($r) => $r->computed_status === 'Available')->sortBy('room_number');

        $availableCount = $availableRooms->count();
        $occupiedCount = $rooms->filter(fn ($r) => $r->computed_status === 'Occupied')->count();
        $cleaningCount = $rooms->filter(fn ($r) => $r->computed_status === 'Cleaning')->count();
        $maintenanceCount = $rooms->filter(fn ($r) => $r->computed_status === 'Maintenance')->count();
        $todayCheckInCount = Booking::whereDate('check_in_time', now('Asia/Jakarta')->toDateString())
            ->whereIn('booking_status', ['Confirmed', 'Check-in'])
            ->count();
        $todayCheckOutCount = Booking::whereDate('check_out_time', now('Asia/Jakarta')->toDateString())
            ->whereIn('booking_status', ['Confirmed', 'Check-in', 'Check-out'])
            ->count();
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
