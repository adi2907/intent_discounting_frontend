<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\SegmentController;
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
Auth::routes(['register' => false]);

Route::get('/', [HomeController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [AppController::class, 'showDashboard'])->name('dashboard');
        Route::get('reloadAnalytics', [AppController::class, 'reloadDashboard'])->name('reload.dashboard');
        Route::get('orderTopVisited', [AppController::class, 'orderTopVisited'])->name('order.top.visited');
        Route::get('orderTopCarted', [AppController::class, 'orderTopCarted'])->name('order.top.carted');
    });

    Route::get('logmeout', [AppController::class, 'logMeOut']);
    Route::get('syncOrders', [AppController::class, 'syncOrders']);

    Route::get('notifications', [AppController::class, 'showNotificationSettings'])->name('notifications');
    Route::get('notifications/smart', [AppController::class, 'showNotificationSettings'])->name('notifications.smart');
    Route::get('notifications/convertAI', [AppController::class, 'smartConvertAI'])->name('notifications.smart.convert.ai');
    Route::get('product_racks', [AppController::class, 'showProductRacks'])->name('productRacks');
    Route::prefix('identified_users')->group(function () {
        Route::get('/', [AppController::class, 'showIdentifiedUsers'])->name('identifiedUsers');
        Route::get('downloadExcel', [AppController::class, 'downloadIdentifiedUsersAsExcel'])->name('downloadIdentifiedUsersExcel');
        Route::get('list', [AppController::class, 'listIdentifiedUsers'])->name('list.identified.users');
    });
    Route::prefix('show/identified_users')->group(function () {
        Route::get('/', [AppController::class, 'showAltIdentifiedUsers'])->name('show.identifiedUsers');
        Route::get('downloadExcel', [AppController::class, 'downloadIdentifiedUsersAsExcel'])->name('show.downloadIdentifiedUsersExcel');
        Route::get('list', [AppController::class, 'listIdentifiedUsers'])->name('show.list.identified.users');
        
        Route::prefix('segments')->group(function () {
            Route::get('create', [SegmentController::class, 'create'])->name('create.identified.user.segments');
            Route::post('/', [SegmentController::class, 'store'])->name('store.identified.user.segments');
            Route::get('/', [SegmentController::class, 'list'])->name('list.identified.user.segments');
            Route::get('delete/{id}', [SegmentController::class, 'delete'])->name('delete.identified.user.segments');
            Route::get('show/{id}', [SegmentController::class, 'show'])->name('show.identified.user.segments');
            
            Route::prefix('partials')->group(function () {
                Route::get('get_did_not_do_events_html', [SegmentController::class, 'getDidNotDoEventsDefaultHTML'])->name('segments.did_not_do_events.defaultHTML');
                Route::get('get_did_do_events_html', [SegmentController::class, 'getDidDoEventsDefaultHTML'])->name('segments.did_do_events.defaultHTML');
            });
        });
    });
    
    Route::post('updateNotificationSettings', [AppController::class, 'updateNotificationSettings'])->name('update.notification.settings');
    Route::post('updateProductRackSettings', [AppController::class, 'updateProductRackSettings'])->name('update.product.rack.settings');

    Route::post('updateProductRacks', [AppController::class, 'updateProductRacks'])->name('update.product.racks');
    Route::post('updateStoreNotifications', [AppController::class, 'updateStoreNotifications'])->name('update.store.notifications');

    Route::get('setup', [AppController::class, 'showSetupPage'])->name('show.setup.page');
    Route::get('turnOnScript', [AppController::class, 'turnAlmeScriptOn'])->name('alme.turn.script.on');  
    Route::get('getPurchaseEvents', [AppController::class, 'getPurchaseEvents']);  
    Route::get('getStores', [AppController::class, 'getListOfStores']);

    Route::get('createNotificationAsset', [AppController::class, 'createNotificationAsset']);
});

Route::get('deleteCustomScript', [AppController::class, 'removeCustomScript']);

Route::prefix('debug')->group(function () {
    Route::get('cron', [AppController::class, 'checkCronStatus']);
    Route::get('checkAlmeScripts', [AppController::class, 'checkAlmeScripts']);
    Route::get('checkAlmeAPIs', [AppController::class, 'checkAlmeAPIs']);
    Route::get('checkShopifyAPIs', [AppController::class, 'checkShopifyAPIs']);
});

Route::middleware('cors')->group(function () {
    Route::get('contact_capture', [AppController::class, 'contactCaptureSettings']);
    Route::get('sale_notification_popup', [AppController::class, 'saleNotificationPopup']);
    Route::get('get_code', [AppController::class, 'getDiscountCodeForStore']);
    Route::get('checkSubmitContact', [AppController::class, 'checkSubmitContact']);
    Route::any('sendCartContents', [AppController::class, 'mapCartContents']);
    Route::get('mapIp', [AppController::class, 'mapIp']);

    //Record DiscountCode copy event 
    Route::get('recordDiscountCodeCopyEvent', [ExtensionController::class, 'recordCopyDiscountCodeEvent']);
    
    Route::post('sendEvent', [AppController::class, 'sendEventToAlme']);
    Route::post('submitContact', [AppController::class, 'submitContact']);
    Route::get('saleNotification', [AppController::class, 'saleNotification']);
    
    //Routes for theme app extensions
    Route::prefix('appExt')->group(function () {
        //New Routes
        Route::post('pickUpWhereYouLeftOff', [ExtensionController::class, 'pickUpWhereYouLeftOff']);
        Route::post('crowdFavorites', [ExtensionController::class, 'crowdFavorites']);
        Route::post('usersAlsoLiked', [ExtensionController::class, 'usersAlsoLiked']);
        Route::post('featuredCollection', [ExtensionController::class, 'featuredCollection']);
        Route::post('cartSuggestions', [ExtensionController::class, 'cartSuggestions']);
    });
});

Route::post('mapCheckout', [AppController::class, 'mapCheckout']);
Route::get('mapCustomer', [AppController::class, 'mapCustomer']);

//Shopify Installation Routes
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

//Shopify Webhooks Routes
Route::prefix('webhooks')->group(function () {
    Route::get('register', [WebhookController::class, 'registerWebhooks'])->name('register.webhooks');
    Route::any('cartUpdate', [WebhookController::class, 'cartUpdateWebhook'])->name('carts.update.webhook');
    Route::any('cartCreate', [WebhookController::class, 'cartCreateWebhook'])->name('carts.create.webhook');
    Route::any('checkoutUpdate', [WebhookController::class, 'checkoutUpdateWebhook'])->name('checkouts.update.webhook');
    Route::any('checkoutCreate', [WebhookController::class, 'checkoutCreateWebhook'])->name('checkouts.create.webhook');
    Route::any('orderCreate', [WebhookController::class, 'orderCreateWebhook'])->name('orders.create.webhook');
    Route::any('orderUpdate', [WebhookController::class, 'orderUpdateWebhook'])->name('orders.update.webhook');

    //For deleting webhooks
    Route::get('deleteWebhooks', [WebhookController::class, 'deleteWebhooks']);
}); 

//Testing scripts
Route::get('sampleDashboard', [HomeController::class, 'sampleDashboard']);
Route::get('sampleProductRack', [HomeController::class, 'sampleProductRack']);
Route::get('checkStoreThemeInstall', [AppController::class, 'checkStoreThemeInstall'])->name('store.check.theme.installation');
Route::get('checkPurchaseEvent/{id}', [HomeController::class, 'checkPurchaseEvent']);
Route::get('deleteCoupons', [HomeController::class, 'deleteCoupons']);
Route::get('testOrders', [HomeController::class, 'testOrders']);
Route::get('full_order/{id}', [HomeController::class, 'testOrder']);
Route::get('testCustomers', [HomeController::class, 'testCustomers']);
Route::get('testPurchaseEvent', [HomeController::class, 'testPurchaseEvent']);
Route::get('testAlmePayload/{id}', [HomeController::class, 'testAlmePayload']);
Route::get('segment_list', [HomeController::class, 'segment_list']);
Route::get('sampleMinOrderCoupon/{id}', [HomeController::class, 'sampleMinOrderCoupon']);
Route::get('testWebhookCache', [HomeController::class, 'testWebhookCache']);
Route::get('createDiscountCode', [HomeController::class, 'testCreateDiscount']);
