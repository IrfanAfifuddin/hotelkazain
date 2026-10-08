<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'receptionist'], true);
    }

    public function view(User $user, Booking $booking): bool
    {
        return in_array($user->role, ['admin', 'receptionist'], true);
    }
}
