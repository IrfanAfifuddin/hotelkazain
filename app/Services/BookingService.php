<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * @param array{
     *     room_quantities?: array<string, int>,
     *     customer_name: string,
     *     phone_number: string,
     *     email: string,
     *     booking_type: string,
     *     check_in_date: string,
     *     check_in_time: string,
     *     duration_hours: int|string,
     *     payment_method: string
     * } $data
     * @return array{customer: Customer, booking: Booking}
     */
    public function createBooking(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            // Handle both room_ids (legacy) and room_quantities (new)
            if (isset($data['room_quantities']) && is_array($data['room_quantities'])) {
                // New multi-room with auto-assignment mode
                $roomQuantities = $data['room_quantities'];
                $checkIn = CarbonImmutable::parse($data['check_in_date'].' '.$data['check_in_time']);
                $nights = $data['booking_type'] === 'Full Day'
                    ? intdiv((int) $data['duration_hours'], 24)
                    : 0;
                $checkOut = $data['booking_type'] === 'Full Day'
                    ? $checkIn->addDays($nights)->setTime(12, 0)
                    : $checkIn->addHours((int) $data['duration_hours']);

                // Get available rooms for each type
                $selectedRooms = collect([]);
                foreach ($roomQuantities as $roomType => $quantity) {
                    $quantity = (int) $quantity;
                    if ($quantity <= 0) continue;

                    // Convert display name ("King Bed") to DB name ("King")
                    $dbType = str_replace(' Bed', '', $roomType);

                    // Find available rooms of this type for the requested time
                    $availableRooms = Room::query()
                        ->where('room_type', $dbType)
                        ->where('room_status', 'Available')
                        ->whereDoesntHave('bookings', function ($query) use ($checkIn, $checkOut) {
                            $query->activeOverlapping($checkIn, $checkOut);
                        })
                        ->orderBy('room_number')
                        ->limit($quantity)
                        ->get();

                    if ($availableRooms->count() < $quantity) {
                        throw ValidationException::withMessages([
                            'room_quantities' => ["Tidak cukup kamar {$roomType} tersedia untuk periode tersebut."],
                        ]);
                    }

                    $selectedRooms = $selectedRooms->concat($availableRooms);
                }

                if ($selectedRooms->isEmpty()) {
                    throw ValidationException::withMessages([
                        'room_quantities' => ['Tidak ada kamar tersedia untuk pilihan Anda.'],
                    ]);
                }

                $rooms = $selectedRooms->shuffle()->values(); // Randomize for variety
            } else {
                // Legacy mode: room_ids provided directly
                $roomIds = $data['room_ids'];

                // Fetch and lock rooms
                $rooms = Room::query()
                    ->whereIn('id', $roomIds)
                    ->lockForUpdate()
                    ->get();

                // Validate all rooms available
                $unavailableRooms = $rooms->filter(fn ($room) => $room->room_status !== 'Available');
                if ($unavailableRooms->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'room_ids' => ['Kamar yang dipilih tidak tersedia.'],
                    ]);
                }
            }

            $checkIn = CarbonImmutable::parse($data['check_in_date'].' '.$data['check_in_time']);
            $nights = $data['booking_type'] === 'Full Day'
                ? intdiv((int) $data['duration_hours'], 24)
                : 0;
            $checkOut = $data['booking_type'] === 'Full Day'
                ? $checkIn->addDays($nights)->setTime(12, 0)
                : $checkIn->addHours((int) $data['duration_hours']);

            // Check overlapping bookings for all rooms (if using room_ids mode)
            if (isset($data['room_ids'])) {
                $roomIds = $data['room_ids'];
                $overlappingRooms = Room::query()
                    ->whereIn('id', $roomIds)
                    ->whereHas('bookings', function ($query) use ($checkIn, $checkOut) {
                        $query->activeOverlapping($checkIn, $checkOut);
                    })
                    ->get();

                if ($overlappingRooms->isNotEmpty()) {
                    $roomNumbers = $overlappingRooms->pluck('room_number')->join(', ');
                    throw ValidationException::withMessages([
                        'room_ids' => ["Kamar No. {$roomNumbers} tidak tersedia untuk slot waktu tersebut."],
                    ]);
                }
            }

            $customer = Customer::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'customer_name' => $data['customer_name'],
                    'phone_number' => $data['phone_number'],
                ],
            );

            // Calculate total price from all rooms
            $totalPrice = 0;
            $roomPrices = [];
            foreach ($rooms as $room) {
                $roomPrice = $data['booking_type'] === 'Short Time'
                    ? (int) $room->{'price_'.(int) $data['duration_hours'].'_hours'}
                    : (int) $room->full_day_price * $nights;
                $totalPrice += $roomPrice;
                $roomPrices[$room->id] = $roomPrice;
            }

            $bookingCode = Booking::generateCode();
            $primaryRoomId = $rooms->first()->id;

            $booking = Booking::create([
                'room_id' => $primaryRoomId,
                'customer_id' => $customer->id,
                'booking_code' => $bookingCode,
                'midtrans_order_id' => 'KZN-PAY-'.Str::uuid(),
                'booking_type' => $data['booking_type'],
                'check_in_time' => $checkIn,
                'check_out_time' => $checkOut,
                'booking_status' => 'Pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'Pending',
                'payment_expires_at' => now()->addMinutes(20),
                'total_price' => $totalPrice,
                'source' => 'Website',
            ]);

            // Save pivot records
            foreach ($rooms as $room) {
                $booking->rooms()->attach($room->id, ['price' => $roomPrices[$room->id]]);
            }

            return [
                'customer' => $customer,
                'booking' => $booking,
            ];
        }, 3);
    }

    public function markPaymentSetupFailed(Booking $booking): void
    {
        $booking->forceFill([
            'booking_status' => 'Cancelled',
            'payment_status' => 'Failed',
        ])->save();
    }
}
