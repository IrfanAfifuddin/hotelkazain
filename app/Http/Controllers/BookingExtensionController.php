<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingExtension;
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

        $checkoutBefore = $booking->check_out_time->copy();
        $checkoutAfter = $validated['extension_type'] === 'Full Day'
            ? $checkoutBefore->copy()->addDay()->startOfDay()->addHours(12)
            : $checkoutBefore->copy()->addHours($duration);

        // Periksa kamar utama dan semua kamar pivot dengan aturan overlap yang sama.
        $roomIds = $booking->rooms->pluck('id')
            ->push($booking->room_id)
            ->filter()
            ->unique()
            ->map(fn (int|string $roomId): int => (int) $roomId)
            ->all();
        $conflictingRoomIds = array_values(array_intersect(
            $roomIds,
            Booking::overlappingRoomIds($checkoutBefore, $checkoutAfter, $booking->id),
        ));

        if ($conflictingRoomIds !== []) {
            throw ValidationException::withMessages(['duration_hours' => 'Kamar tidak tersedia untuk durasi perpanjangan tersebut karena berbenturan dengan pemesanan lain.']);
        }

        // Calculate total extension amount untuk semua rooms (multi-room support)

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

        $paymentStatus = $validated['payment_method'] === 'Cash' ? 'Paid' : 'Pending';
        $paymentTime = $paymentStatus === 'Paid' ? now('Asia/Jakarta') : null;

        DB::transaction(function () use ($booking, $validated, $duration, $amount, $checkoutBefore, $checkoutAfter, $paymentStatus, $paymentTime): void {
            $booking->update([
                'check_out_time' => $checkoutAfter,
                'total_price' => $booking->total_price + $amount,
            ]);

            BookingExtension::create([
                'booking_id' => $booking->id,
                'extension_type' => $validated['extension_type'],
                'duration_hours' => $duration,
                'amount' => $amount,
                'check_out_before' => $checkoutBefore,
                'check_out_after' => $checkoutAfter,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $paymentStatus,
                'payment_time' => $paymentTime,
            ]);
        });

        $prefix = auth()->user()->role === 'Admin' ? 'admin' : 'receptionist';

        return redirect()->route($prefix.'.pemesanan.index')->with('success', 'Extend berhasil disimpan. Total biaya: Rp '.number_format($amount, 0, ',', '.'));
    }
}
