<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\WebhookController;
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
    Route::get('contact_capture', [AppController::class, 'contactCaptureSettings']);
    Route::get('sale_notification_popup', [AppController::class, 'saleNotificationPopup']);
    Route::get('get_code', [AppController::class, 'getDiscountCodeForStore']);
    Route::get('checkSubmitContact', [AppController::class, 'checkSubmitContact']);
    Route::any('sendCartContents', [AppController::class, 'mapCartContents']);
    
    //Routes for theme app extensions
    Route::prefix('appExt')->group(function () {
        /*
        Route::post('mostViewed', [ExtensionController::class, 'getMostViewedData']);
        Route::post('mostCarted', [ExtensionController::class, 'getMostCartedData']);
        Route::post('carts', [ExtensionController::class, 'carts']);
        Route::post('recommendedForYou', [ExtensionController::class, 'recommendedForYou']);
        Route::post('userLiked', [ExtensionController::class, 'userLiked']);
        */
        //New Routes
        Route::post('pickUpWhereYouLeftOff', [ExtensionController::class, 'pickUpWhereYouLeftOff']);
        Route::post('crowdFavorites', [ExtensionController::class, 'crowdFavorites']);
        Route::post('usersAlsoLiked', [ExtensionController::class, 'usersAlsoLiked']);
        Route::post('featuredCollection', [ExtensionController::class, 'featuredCollection']);
        
    });
});

Route::prefix('shopify/auth')->group(function () {
    Route::get('/', [InstallationController::class, 'startInstallation']);
    Route::get('redirect', [InstallationController::class, 'handleRedirect'])->name('shopify.auth.redirect');
});

//Mandatory GDPR Webhooks
Route::prefix('gdpr/webhooks')->group(function () {
    Route::any('customer_data_request', [WebhookController::class, 'handleCustomerDataRequest']);
    Route::any('customer_data_erasure', [WebhookController::class, 'handleCustomerDataErasure']);
    Route::any('shop_data_erasure', [WebhookController::class, 'handleShopDataErasure']);
});

Route::prefix('webhooks')->group(function () {
    Route::get('register', [WebhookController::class, 'registerWebhooks'])->name('register.webhooks');
    Route::any('cartUpdate', [WebhookController::class, 'cartUpdateWebhook'])->name('carts.update.webhook');
    Route::any('cartCreate', [WebhookController::class, 'cartCreateWebhook'])->name('carts.create.webhook');
    Route::any('checkoutUpdate', [WebhookController::class, 'checkoutUpdateWebhook'])->name('checkouts.update.webhook');
    Route::any('checkoutCreate', [WebhookController::class, 'checkoutCreateWebhook'])->name('checkouts.create.webhook');
    Route::any('orderCreate', [WebhookController::class, 'orderCreateWebhook'])->name('orders.create.webhook');
    Route::any('orderUpdate', [WebhookController::class, 'orderUpdateWebhook'])->name('orders.update.webhook');
}); 

//Testing scripts
Route::get('sampleDashboard', [HomeController::class, 'sampleDashboard']);
Route::get('sampleProductRack', [HomeController::class, 'sampleProductRack']);
Route::get('checkStoreThemeInstall', [AppController::class, 'checkStoreThemeInstall'])->name('store.check.theme.installation');
Route::get('checkPurchaseEvent', [HomeController::class, 'checkPurchaseEvent']);