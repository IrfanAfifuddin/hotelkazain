<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Tampilkan daftar status kamar (bisa diakses Admin & Resepsionis).
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Room::query();

        if ($status && in_array($status, ['Available', 'Occupied', 'Cleaning'])) {
            $query->where('room_status', $status);
        }

        $rooms = $query->orderBy('room_number')->get();

        // Hitung statistik ruangan
        $stats = [
            'total' => Room::count(),
            'available' => Room::where('room_status', 'Available')->count(),
            'occupied' => Room::where('room_status', 'Occupied')->count(),
            'cleaning' => Room::where('room_status', 'Cleaning')->count(),
        ];

        return view('rooms.index', compact('rooms', 'stats', 'status'));
    }

    /**
     * Perbarui status kamar secara manual (Misal: selesai dibersihkan jadi Available).
     */
    public function updateStatus(Request $request, Room $room)
    {
        $request->validate([
            'room_status' => 'required|in:Available,Occupied,Cleaning',
        ]);

        $room->update([
            'room_status' => $request->room_status,
        ]);

        $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';
        return redirect()->route($prefix . '.status-kamar.index')->with('success', 'Status Kamar ' . $room->room_number . ' berhasil diperbarui.');
    }

    /**
     * Proses Perpanjangan Waktu Inap (Extend Booking) dengan validasi konflik jadwal.
     */
    public function extend(Request $request, Room $room)
    {
        $request->validate([
            'hours' => 'required|integer|min:1|max:24',
        ]);

        $booking = $room->bookings()
            ->where('booking_status', 'Check-in')
            ->latest('check_in_time')
            ->first();

        if (!$booking) {
            return back()->withErrors(['extend' => 'Kamar tidak memiliki booking aktif untuk diperpanjang.']);
        }

        $hours = (int) $request->hours;
        $currentCheckOut = Carbon::parse($booking->check_out_time);
        $newCheckOut = (clone $currentCheckOut)->addHours($hours);

        $conflict = Booking::where('room_id', $booking->room_id)
            ->where('id', '!=', $booking->id)
            ->whereIn('booking_status', ['Pending', 'Confirmed', 'Check-in'])
            ->where(function ($query) use ($currentCheckOut, $newCheckOut) {
                $query->where('check_in_time', '<', $newCheckOut)
                    ->where('check_out_time', '>', $currentCheckOut);
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['extend' => 'Gagal memperpanjang waktu: Kamar sudah dipesan oleh tamu lain pada rentang waktu tersebut.']);
        }

        $additionalPrice = round(($room->price_5_hours / 5) * $hours);

        $booking->update([
            'check_out_time' => $newCheckOut,
            'total_price' => $booking->total_price + $additionalPrice,
        ]);

        $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';

        return redirect()->route($prefix . '.status-kamar.index')->with('success', 'Waktu inap berhasil diperpanjang +' . $hours . ' jam.');
    }
}
