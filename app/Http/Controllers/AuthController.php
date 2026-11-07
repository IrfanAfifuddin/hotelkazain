<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    /**
     * Proses login: validasi username + password, cek role.
     */
    public function processLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            // Cek apakah role adalah Admin atau Receptionist
            $user = Auth::user();
            if (! in_array($user->role, ['Admin', 'Receptionist'])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'username' => 'Akses Ditolak',
                ])->onlyInput('username');
            }

            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'username' => 'Username atau Password Salah',
        ])->onlyInput('username');
    }

    /**
     * Dashboard — menampilkan dashboard sesuai role.
     */
    public function dashboard()
    {
        $user = Auth::user();

        if ($user->role === 'Admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'Receptionist') {
            return redirect()->route('receptionist.dashboard');
        }

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

        return view('dashboard', compact(
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

    /**
     * Proses logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
