<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function index()
    {
        return view('customer.checkout');
    }
}
