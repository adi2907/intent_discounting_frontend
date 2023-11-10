<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\InstallationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [AppController::class, 'showDashboard'])->name('dashboard');
    Route::get('notifications', [AppController::class, 'showNotificationSettings'])->name('notifications');
    Route::get('product_racks', [AppController::class, 'showProductRacks'])->name('productRacks');
    Route::post('updateNotificationSettings', [AppController::class, 'updateNotificationSettings'])->name('update.notification.settings');
    Route::post('updateProductRackSettings', [AppController::class, 'updateProductRackSettings'])->name('update.product.rack.settings');
});

Route::get('deleteCustomScript', [AppController::class, 'removeCustomScript']);

Route::prefix('debug')->group(function () {
    Route::get('cron', [AppController::class, 'checkCronStatus']);
});

Route::middleware('cors')->group(function () {
    Route::get('theme_popups', [AppController::class, 'themePopups']);
    Route::get('get_code', [AppController::class, 'getDiscountCodeForStore']);
});

Route::prefix('shopify/auth')->group(function () {
    Route::get('/', [InstallationController::class, 'startInstallation']);
    Route::get('redirect', [InstallationController::class, 'handleRedirect'])->name('shopify.auth.redirect');
});