<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingExtension;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingExtensionController extends Controller
{
    public function create(Booking $booking): View
    {
        $booking->load(['customer', 'rooms']);

        abort_unless($booking->booking_status === 'Check-in', 422, 'Hanya pemesanan aktif yang dapat diperpanjang.');

        return view('bookings.extend', compact('booking'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'extension_type' => 'required|in:Short Time,Full Day',
            'duration_hours' => 'required|integer|in:5,8,24',
            'payment_method' => 'required|in:Cash,Transfer',
        ]);

        $booking->load(['rooms', 'customer']);
        abort_unless($booking->booking_status === 'Check-in', 422, 'Hanya pemesanan aktif yang dapat diperpanjang.');

        $duration = (int) $validated['duration_hours'];
        if ($validated['extension_type'] === 'Short Time' && ! in_array($duration, [5, 8], true)) {
            throw ValidationException::withMessages(['duration_hours' => 'Durasi Short Time hanya 5 atau 8 jam.']);
        }

        if ($validated['extension_type'] === 'Full Day' && $duration !== 24) {
            throw ValidationException::withMessages(['duration_hours' => 'Durasi Full Day harus 24 jam.']);
        }

        $checkoutBefore = Carbon::parse($booking->check_out_time, 'Asia/Jakarta');
        $checkoutAfter = $validated['extension_type'] === 'Full Day'
            ? $checkoutBefore->copy()->addDay()->startOfDay()->addHours(12)
            : $checkoutBefore->copy()->addHours($duration);

        // Check conflict untuk semua rooms dalam multi-room booking
        $roomIds = $booking->rooms()->pluck('room_id')->toArray();
        $conflict = Booking::query()
            ->whereIn('room_id', $roomIds)
            ->where('id', '!=', $booking->id)
            ->whereIn('booking_status', ['Pending', 'Confirmed', 'Check-in'])
            ->where('check_in_time', '<', $checkoutAfter)
            ->where('check_out_time', '>', $checkoutBefore)
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages(['duration_hours' => 'Kamar tidak tersedia untuk durasi perpanjangan tersebut karena berbenturan dengan pemesanan lain.']);
        }

        // Calculate total extension amount untuk semua rooms (multi-room support)
        $extensionRate = match ([$validated['extension_type'], $duration]) {
            ['Short Time', 5] => 5 / 5,
            ['Short Time', 8] => 8 / 8,
            default => 24 / 24,
        };

        $amount = $booking->rooms()
            ->get()
            ->reduce(function ($sum, $room) use ($validated) {
                $roomPrice = match ([$validated['extension_type'], (int) $validated['duration_hours']]) {
                    ['Short Time', 5] => $room->price_5_hours,
                    ['Short Time', 8] => $room->price_8_hours,
                    default => $room->full_day_price,
                };

                return $sum + $roomPrice;
            }, 0);

        DB::transaction(function () use ($booking, $validated, $duration, $amount, $checkoutBefore, $checkoutAfter): void {
            $booking->update(['check_out_time' => $checkoutAfter]);

            BookingExtension::create([
                'booking_id' => $booking->id,
                'extension_type' => $validated['extension_type'],
                'duration_hours' => $duration,
                'amount' => $amount,
                'check_out_before' => $checkoutBefore,
                'check_out_after' => $checkoutAfter,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'Cash' ? 'Paid' : 'Pending',
                'payment_time' => $validated['payment_method'] === 'Cash' ? now('Asia/Jakarta') : null,
            ]);
        });

        $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';

        return redirect()->route($prefix.'.pemesanan.index')->with('success', 'Extend berhasil disimpan. Total biaya: Rp '.number_format($amount, 0, ',', '.'));
    }
}
