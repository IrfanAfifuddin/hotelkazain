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
     * Status dihitung otomatis berdasarkan waktu check-in/check-out dan masa cleaning 1 jam.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Room::query()->with(['bookings' => function ($q) {
            $q->whereIn('booking_status', ['Confirmed', 'Check-in', 'Check-out'])
                ->orderBy('check_in_time', 'desc');
        }])->orderBy('room_number');

        $rooms = $query->get()->map(function ($room) {
            // Compute current status on-the-fly
            $room->current_status = $room->computed_status;
            $room->cleaning_ends_at = $room->cleaning_ends_at;
            $room->active_booking = $room->active_booking;

            return $room;
        });

        if ($status && in_array($status, ['Available', 'Occupied', 'Cleaning'])) {
            $rooms = $rooms->filter(fn ($room) => $room->current_status === $status);
        }

        // Hitung statistik ruangan berdasarkan status terkomputasi
        $stats = [
            'total' => Room::count(),
            'available' => $rooms->where('current_status', 'Available')->count(),
            'occupied' => $rooms->where('current_status', 'Occupied')->count(),
            'cleaning' => $rooms->where('current_status', 'Cleaning')->count(),
        ];

        return view('rooms.index', compact('rooms', 'stats', 'status'));
    }

    /**
     * Perbarui status kamar secara manual (Misal: selesai dibersihkan jadi Available).
     * Validasi: tidak boleh mengubah ke Available jika masih ada booking aktif atau cleaning belum selesai.
     */
    public function updateStatus(Request $request, Room $room)
    {
        $request->validate([
            'room_status' => 'required|in:Available,Occupied,Cleaning',
        ]);

        $requestedStatus = $request->room_status;
        $computedStatus = $room->computed_status;

        // Prevent manual override to Available if room should be Occupied (cleaning can be manually overridden to Available)
        if ($requestedStatus === 'Available' && $computedStatus === 'Occupied') {
            $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';

            return redirect()->route($prefix.'.status-kamar.index')
                ->withErrors(['room_status' => 'Kamar tidak bisa dijadikan Tersedia karena masih ada tamu yang menginap (Terisi).'])
                ->withInput();
        }

        // Prevent manual override to Occupied if no active booking
        if ($requestedStatus === 'Occupied' && $computedStatus === 'Available') {
            $activeBooking = $room->active_booking;
            if (! $activeBooking) {
                $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';

                return redirect()->route($prefix.'.status-kamar.index')
                    ->withErrors(['room_status' => 'Kamar tidak bisa dijadikan Terisi karena tidak ada booking aktif.'])
                    ->withInput();
            }
        }

        $room->update([
            'room_status' => $requestedStatus,
        ]);

        $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';

        return redirect()->route($prefix.'.status-kamar.index')->with('success', 'Status Kamar '.$room->room_number.' berhasil diperbarui.');
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

        if (! $booking) {
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

        return redirect()->route($prefix.'.status-kamar.index')->with('success', 'Waktu inap berhasil diperpanjang +'.$hours.' jam.');
    }
}
