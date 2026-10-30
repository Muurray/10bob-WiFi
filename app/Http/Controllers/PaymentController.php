<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(): View
    {
        return view('customer.pay');
    }
}