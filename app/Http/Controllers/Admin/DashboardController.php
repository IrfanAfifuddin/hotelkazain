<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Admin lengkap beserta data kamar tersedia untuk form pemesanan.
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
        $todayCheckInCount = Booking::whereDate('check_in_time', now('Asia/Jakarta')->toDateString())
            ->whereIn('booking_status', ['Confirmed', 'Check-in'])
            ->count();
        $todayCheckOutCount = Booking::whereDate('check_out_time', now('Asia/Jakarta')->toDateString())
            ->whereIn('booking_status', ['Confirmed', 'Check-in', 'Check-out'])
            ->count();
        $now = Carbon::now('Asia/Jakarta');
        $recentBookings = Booking::with(['customer', 'room'])
            ->where('check_in_time', '<=', $now)
            ->where('check_out_time', '>=', $now)
            ->whereNotIn('booking_status', ['Cancelled'])
            ->orderBy('check_in_time', 'desc')
            ->get();

        return view('dashboard', compact(
            'availableRooms',
            'availableCount',
            'occupiedCount',
            'cleaningCount',
            'todayCheckInCount',
            'todayCheckOutCount',
            'recentBookings'
        ));
    }
}
