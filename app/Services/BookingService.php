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
     *     room_id: int|string,
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
            $room = Room::query()
                ->whereKey((int) $data['room_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($room->room_status !== 'Available') {
                throw ValidationException::withMessages([
                    'room_id' => ['The selected room is not available for that time slot.'],
                ]);
            }

            $checkIn = CarbonImmutable::parse($data['check_in_date'].' '.$data['check_in_time']);
            $nights = $data['booking_type'] === 'Full Day'
                ? intdiv((int) $data['duration_hours'], 24)
                : 0;
            $checkOut = $data['booking_type'] === 'Full Day'
                ? $checkIn->addDays($nights)->setTime(12, 0)
                : $checkIn->addHours((int) $data['duration_hours']);

            $hasOverlappingBooking = Booking::query()
                ->where('room_id', $room->id)
                ->activeOverlapping($checkIn, $checkOut)
                ->exists();

            if ($hasOverlappingBooking) {
                throw ValidationException::withMessages([
                    'room_id' => ['The selected room is not available for that time slot.'],
                ]);
            }

            $customer = Customer::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'customer_name' => $data['customer_name'],
                    'phone_number' => $data['phone_number'],
                ],
            );

            $totalPrice = $data['booking_type'] === 'Short Time'
                ? (int) $room->{'price_'.(int) $data['duration_hours'].'_hours'}
                : (int) $room->full_day_price * $nights;
            $bookingCode = Booking::generateCode();

            $booking = $room->bookings()->create([
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
