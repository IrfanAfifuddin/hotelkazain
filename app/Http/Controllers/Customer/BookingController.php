<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    public function availability()
    {
        return view('customer.availability');
    }

    public function checkout()
    {
        return view('customer.checkout');
    }

    public function success()
    {
        return view('customer.success');
    }
}
