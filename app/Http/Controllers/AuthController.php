<?php

namespace App\Http\Controllers;

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
        $availableRooms = \App\Models\Room::where('room_status', 'Available')->orderBy('room_number')->get();
        $availableCount = $availableRooms->count();
        $occupiedCount = \App\Models\Room::where('room_status', 'Occupied')->count();
        $todayCheckInCount = \App\Models\Booking::whereDate('check_in_time', today())->count();
        $todayCheckOutCount = \App\Models\Booking::whereDate('check_out_time', today())->count();
        $recentBookings = \App\Models\Booking::with(['customer', 'room'])->latest()->take(5)->get();

        return view('dashboard', compact(
            'availableRooms',
            'availableCount',
            'occupiedCount',
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
