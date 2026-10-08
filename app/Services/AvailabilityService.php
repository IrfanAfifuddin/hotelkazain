<?php

namespace App\Services;

class AvailabilityService
{
    public function checkAvailability(string $date): array
    {
        return [
            'date' => $date,
            'rooms' => [],
        ];
    }
}
