<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use Carbon\CarbonImmutable;

class AvailabilityService
{
    public function checkAvailability(string $date): array
    {
        $start = CarbonImmutable::parse($date)->startOfDay();
        $end = $start->addDay();
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
}
