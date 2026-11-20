<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
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
    public function processLogin(LoginRequest $request)
    {
        $request->ensureIsNotRateLimited();

        if (Auth::attempt($request->only('username', 'password'))) {
            $request->clearRateLimiter();

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

        $request->hitRateLimiter();

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

        $now = Carbon::now('Asia/Jakarta');
        $defaultCheckOut = (clone $now);
        if ($now->hour >= 12) {
            $defaultCheckOut->addDay();
        }
        $defaultCheckOut->setTime(12, 0);
        $unavailableRoomIds = Booking::overlappingRoomIds($now, $defaultCheckOut);

        $availableRooms = $rooms
            ->filter(fn ($r) => $r->computed_status === 'Available'
                && ! in_array($r->id, $unavailableRoomIds, true))
            ->sortBy('room_number');

        $availableCount = $availableRooms->count();
        $occupiedCount = $rooms->filter(fn ($r) => $r->computed_status === 'Occupied')->count();
        $cleaningCount = $rooms->filter(fn ($r) => $r->computed_status === 'Cleaning')->count();
        $todayCheckInCount = Booking::whereDate('check_in_time', now('Asia/Jakarta')->toDateString())
            ->whereIn('booking_status', ['Confirmed', 'Check-in'])
            ->count();
        $todayCheckOutCount = Booking::whereDate('check_out_time', now('Asia/Jakarta')->toDateString())
            ->whereIn('booking_status', ['Confirmed', 'Check-in', 'Check-out'])
            ->count();
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

    /**
     * Tolak akses GET ke logout dengan 405 sambil tetap menjalankan middleware web,
     * sehingga status autentikasi pengguna dapat dibaca oleh halaman error.
     */
    public function rejectGetLogout(): never
    {
        abort(405, 'Metode Tidak Diizinkan');
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
