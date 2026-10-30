<?php
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\PaymentController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PackageController;

Route::resource('/admin/packages', PackageController::class);

Route::get('/', function () {
    return view('customer.index');
});

Route::get('/voucher', function () {
    return view('customer.voucher');
});

Route::get('/buy', [PaymentController::class, 'show'])->name('payments.show');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::get('/admin/login', function () {
    return view('admin.login');
});

Route::post('/admin/login', [DashboardController::class, 'index']);

Route::get('/admin/dashboard', [DashboardController::class, 'index']);