<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomerBookingController extends Controller
{
    public function availability(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        $rooms = Room::all()->map(function (Room $room) use ($date) {
            return [
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => $room->room_type,
                'room_status' => $this->getRoomStatusForDate($room, $date),
                'price_5_hours' => $room->price_5_hours,
                'price_8_hours' => $room->price_8_hours,
                'full_day_price' => $room->full_day_price,
            ];
        });

        return response()->json([
            'data' => $rooms,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'booking_type' => ['required', 'in:Short Time,Full Day'],
            'check_in_date' => ['required', 'date_format:Y-m-d'],
            'check_in_time' => ['required', 'date_format:H:i'],
            'duration_hours' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'in:Virtual Account,QRIS,Cash'],
            'payment_status' => ['required', 'in:Pending,Paid,Failed,Expired,Cancelled'],
            'agree_terms' => ['accepted'],
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $room = Room::findOrFail($request->room_id);
        $checkIn = Carbon::parse($request->check_in_date . ' ' . $request->check_in_time);
        $checkOut = $checkIn->copy()->addHours((int) $request->duration_hours);

        if ($this->hasOverlappingBooking($room->id, $checkIn, $checkOut)) {
            throw ValidationException::withMessages([
                'room_id' => ['The selected room is not available for that time slot.'],
            ]);
        }

        $customer = Customer::firstOrCreate([
            'email' => $request->email,
        ], [
            'customer_name' => $request->customer_name,
            'phone_number' => $request->phone_number,
        ]);

        $booking = Booking::create([
            'room_id' => $room->id,
            'customer_id' => $customer->id,
            'booking_code' => Booking::generateCode(),
            'booking_type' => $request->booking_type,
            'check_in_time' => $checkIn,
            'check_out_time' => $checkOut,
            'booking_status' => 'Pending',
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_status,
            'payment_time' => $request->payment_status === 'Paid' ? now() : null,
            'total_price' => $this->calculateTotalPrice($room, $request->booking_type, (int) $request->duration_hours),
            'source' => 'Website',
        ]);

        return response()->json([
            'message' => 'Booking created successfully.',
            'data' => [
                'customer' => $customer,
                'booking' => $booking,
            ],
        ], 201);
    }

    private function getRoomStatusForDate(Room $room, string $date): string
    {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        $hasActiveBooking = Booking::where('room_id', $room->id)
            ->whereIn('booking_status', ['Pending', 'Confirmed', 'Check-in'])
            ->where('check_in_time', '<', $endOfDay)
            ->where('check_out_time', '>', $startOfDay)
            ->exists();

        return $hasActiveBooking ? 'Booked' : 'Available';
    }

    private function hasOverlappingBooking(int $roomId, Carbon $newStart, Carbon $newEnd): bool
    {
        return Booking::where('room_id', $roomId)
            ->whereIn('booking_status', ['Pending', 'Confirmed', 'Check-in'])
            ->where('check_in_time', '<', $newEnd)
            ->where('check_out_time', '>', $newStart)
            ->exists();
    }

    private function calculateTotalPrice(Room $room, string $bookingType, int $durationHours): int
    {
        if ($bookingType === 'Short Time') {
            return $durationHours === 5 ? (int) $room->price_5_hours : (int) $room->price_8_hours;
        }

        return (int) $room->full_day_price;
    }
}
