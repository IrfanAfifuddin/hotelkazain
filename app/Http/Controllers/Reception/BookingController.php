<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    public function index()
    {
        return view('receptionist.bookings.index');
    }
}
