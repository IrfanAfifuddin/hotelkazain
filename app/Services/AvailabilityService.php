<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use Carbon\CarbonImmutable;

class AvailabilityService
{
    public function checkAvailability(
        string $date,
        ?string $checkInTime = null,
        ?string $bookingType = null,
        ?int $durationHours = null,
    ): array {
        $start = CarbonImmutable::parse($date)->startOfDay();
        $start = $checkInTime === null
            ? $start
            : CarbonImmutable::parse($date.' '.$checkInTime);
        $end = match (true) {
            $checkInTime === null => $start->addDay(),
            $bookingType === 'Full Day' => $start->addDays(intdiv((int) $durationHours, 24))->setTime(12, 0),
            default => $start->addHours((int) $durationHours),
        };
        $bookedRoomIds = Booking::query()
            ->activeOverlapping($start, $end)
            ->distinct()
            ->pluck('room_id')
            ->map(fn (int|string $roomId): int => (int) $roomId)
            ->all();

        $rooms = Room::query()
            ->orderBy('room_number')
            ->get([
                'id',
                'room_number',
                'room_type',
                'room_status',
                'price_5_hours',
                'price_8_hours',
                'full_day_price',
            ])
            ->map(function (Room $room) use ($bookedRoomIds): array {
                $hasActiveBooking = in_array($room->id, $bookedRoomIds, true);
                $isAvailable = $room->room_status === 'Available' && ! $hasActiveBooking;

                return [
                    'room_id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_type' => $room->room_type,
                    'room_status' => $hasActiveBooking ? 'Booked' : $room->room_status,
                    'is_available' => $isAvailable,
                    'price_5_hours' => $room->price_5_hours,
                    'price_8_hours' => $room->price_8_hours,
                    'full_day_price' => $room->full_day_price,
                ];
            });

        $roomTypes = $rooms
            ->groupBy('room_type')
            ->map(fn ($rooms): array => [
                'total' => $rooms->count(),
                'available' => $rooms->where('is_available', true)->count(),
            ]);

        return [
            'date' => $start->toDateString(),
            'data' => $rooms->values()->all(),
            'summary' => [
                'total_rooms' => $rooms->count(),
                'available_rooms' => $rooms->where('is_available', true)->count(),
                'by_room_type' => $roomTypes->all(),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function roomTypes(): array
    {
        return Room::query()
            ->orderBy('room_number')
            ->get([
                'room_type',
                'price_5_hours',
                'price_8_hours',
                'full_day_price',
            ])
            ->groupBy('room_type')
            ->map(function ($rooms, string $type): array {
                $room = $rooms->first();
                $shortName = $type === 'King' ? 'King Bed' : $type.' Bed';

                return [
                    'type' => $type,
                    'name' => 'Kamar '.$shortName,
                    'short_name' => $shortName,
                    'description' => 'Kamar '.$type.' untuk 1–2 orang',
                    'capacity' => 2,
                    'total_rooms' => $rooms->count(),
                    'prices' => [
                        '5' => (int) $room->price_5_hours,
                        '8' => (int) $room->price_8_hours,
                        'full' => (int) $room->full_day_price,
                    ],
                    'facilities' => ['Kamar Mandi Dalam', 'Air Panas', 'AC', 'Wi-Fi Gratis'],
                    'image' => file_exists(public_path('images/kamar-'.strtolower($type).'.jpg'))
                        ? asset('images/kamar-'.strtolower($type).'.jpg')
                        : null,
                ];
            })
            ->values()
            ->all();
    }
}
