<?php

namespace App\Services;

class BookingService
{
    public function createBooking(array $data): array
    {
        return [
            'status' => 'draft',
            'data' => $data,
        ];
    }
}
