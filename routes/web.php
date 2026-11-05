<?php
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\PortalController;

Route::resource('/admin/packages', PackageController::class);

Route::get('/', [PortalController::class, 'packages'])->name('home');
Route::get('/buy/packages', [PortalController::class, 'packages'])->name('portal.packages');
Route::get('/terms', [PortalController::class, 'terms'])->name('terms');

Route::get('/voucher', function () {
    return view('customer.voucher');
});

Route::get('/buy', fn () => redirect('/#packages'));
Route::get('/buy/{package}', [PaymentController::class, 'show'])->name('payments.show');
Route::post('/buy/{package}', [PaymentController::class, 'initialize'])->name('payments.initialize');
Route::get('/payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');

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