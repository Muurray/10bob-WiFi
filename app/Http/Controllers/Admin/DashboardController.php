<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $customers = Customer::count();

        $active = Customer::where('active', true)->count();

        $sales = Payment::where('status', 'Success')
            ->whereDate('created_at', today())
            ->sum('amount');

        $expired = Customer::where('active', false)->count();

        return view('admin.dashboard', compact(
            'customers',
            'active',
            'sales',
            'expired'
        ));
    }
}