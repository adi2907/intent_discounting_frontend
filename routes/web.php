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
| Alme Routes as laid out for the Shopify App.
| It's broken down by their use cases.
|
*/

//Register route is false, because there is no external registration in the scope of the app.
Auth::routes(['register' => false]);

//Index route
Route::get('/', [HomeController::class, 'index']);

//Middleware auth is applied to these routes. User must be authenticated in order to be able to access these routes.
Route::middleware(['auth', 'ensureShopIsPaid'])->group(function () {
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [AppController::class, 'showDashboard'])->name('dashboard');
        Route::get('reloadAnalytics', [AppController::class, 'reloadDashboard'])->name('reload.dashboard');
        
        //Probably not used anymore. Remove with Caution!
        Route::get('orderTopVisited', [AppController::class, 'orderTopVisited'])->name('order.top.visited');
        Route::get('orderTopCarted', [AppController::class, 'orderTopCarted'])->name('order.top.carted');
    });

    //Logout route provided seperately. Because logout function isn't provided in the UI for merchants.
    Route::get('logmeout', [AppController::class, 'logMeOut']);

    //Testing script in case you want to manually sync orders for authenticated user.
    Route::get('syncOrders', [AppController::class, 'syncOrders']);

    //This route is probably not used anymore. Remove with Caution!
    Route::get('notifications', [AppController::class, 'showNotificationSettings'])->name('notifications');
    
    //Smart Recognize and Smart Convert page routes 
    Route::get('notifications/smart', [AppController::class, 'showNotificationSettings'])->name('notifications.smart');
    Route::get('notifications/convertAI', [AppController::class, 'smartConvertAI'])->name('notifications.smart.convert.ai');
    
    //Probably not used anymore. Remove with Caution!
    Route::get('product_racks', [AppController::class, 'showProductRacks'])->name('productRacks');
    
    //Identified Users page routes. Old flow. Remove with Caution!
    Route::prefix('identified_users')->group(function () {
        Route::get('/', [AppController::class, 'showIdentifiedUsers'])->name('identifiedUsers');
        Route::get('downloadExcel', [AppController::class, 'downloadIdentifiedUsersAsExcel'])->name('downloadIdentifiedUsersExcel');
        Route::get('list', [AppController::class, 'listIdentifiedUsers'])->name('list.identified.users');
    });

    //Currently these routes are used for identified users page.
    Route::prefix('show/identified_users')->group(function () {
        Route::get('/', [AppController::class, 'showAltIdentifiedUsers'])->name('show.identifiedUsers');
        Route::get('downloadExcel', [AppController::class, 'downloadIdentifiedUsersAsExcel'])->name('show.downloadIdentifiedUsersExcel');
        Route::get('list', [AppController::class, 'listIdentifiedUsers'])->name('show.list.identified.users');
        
        //Segments page routes
        Route::prefix('segments')->group(function () {
            Route::get('create', [SegmentController::class, 'create'])->name('create.identified.user.segments');
            Route::post('/', [SegmentController::class, 'store'])->name('store.identified.user.segments');
            Route::get('/', [SegmentController::class, 'list'])->name('list.identified.user.segments');
            Route::get('delete/{id}', [SegmentController::class, 'delete'])->name('delete.identified.user.segments');
            Route::get('show/{id}', [SegmentController::class, 'show'])->name('show.identified.user.segments');
            
            //Routes for getting partial HTMLs for rendering.
            Route::prefix('partials')->group(function () {
                Route::get('get_did_not_do_events_html', [SegmentController::class, 'getDidNotDoEventsDefaultHTML'])->name('segments.did_not_do_events.defaultHTML');
                Route::get('get_did_do_events_html', [SegmentController::class, 'getDidDoEventsDefaultHTML'])->name('segments.did_do_events.defaultHTML');
            });
        });
    });
    
    //Save notification settings route
    Route::post('updateNotificationSettings', [AppController::class, 'updateNotificationSettings'])->name('update.notification.settings');
    
    //Probably these are not used anymore. Remove with Caution!
    Route::post('updateProductRackSettings', [AppController::class, 'updateProductRackSettings'])->name('update.product.rack.settings');
    Route::post('updateProductRacks', [AppController::class, 'updateProductRacks'])->name('update.product.racks');
    Route::post('updateStoreNotifications', [AppController::class, 'updateStoreNotifications'])->name('update.store.notifications');

    //Routes for managing the script that merchants need to enable on their theme editor.
    Route::get('setup', [AppController::class, 'showSetupPage'])->name('show.setup.page');

    //Not used anymore. Shopify doesn't allow you to manually make changes directly to theme. Remove with Caution!
    Route::get('turnOnScript', [AppController::class, 'turnAlmeScriptOn'])->name('alme.turn.script.on');  

    //Manually call purchase event API of Alme backend.
    Route::get('getPurchaseEvents', [AppController::class, 'getPurchaseEvents']);
    
    //Just for testing. Might be useful. You can remove it.
    Route::get('getStores', [AppController::class, 'getListOfStores']);

    //Just for testing. In case the notification asset did not get created properly. So you can hit it manually.
    Route::get('createNotificationAsset', [AppController::class, 'createNotificationAsset']);
});

//Accept charge route. Should not be in EnsureStoreIsPaid middleware
Route::middleware('auth')->get('acceptCharge', [AppController::class, 'acceptCharge'])->name('shopify.accept.charge');

//Not used anymore. Used script tags API that we don't use in the app.
Route::get('deleteCustomScript', [AppController::class, 'removeCustomScript']);

//Debug routes. In case you want to check certain specific things.
Route::prefix('debug')->group(function () {
    Route::get('cron', [AppController::class, 'checkCronStatus']);
    Route::get('checkAlmeScripts', [AppController::class, 'checkAlmeScripts']);
    Route::get('checkAlmeAPIs', [AppController::class, 'checkAlmeAPIs']);
    Route::get('checkShopifyAPIs', [AppController::class, 'checkShopifyAPIs']);
});

//These routes are called on the frontend. On the merchant's store to render content.
//Cors middleware is used because it's the frontend that's calling these routes.
Route::middleware('cors')->group(function () {

    //Contact Capture route. Renders HTML.
    Route::get('contact_capture', [AppController::class, 'contactCaptureSettings']);
    Route::get('sale_notification_popup', [AppController::class, 'saleNotificationPopup']);
    
    //Not used anymore.
    Route::get('get_code', [AppController::class, 'getDiscountCodeForStore']);
    
    //Not used anymore.
    Route::get('checkSubmitContact', [AppController::class, 'checkSubmitContact']);
    
    //Important route. Allows the users cart to be mapped onto the database.
    Route::any('sendCartContents', [AppController::class, 'mapCartContents']);

    //Important route. Maps users IP address based on almetoken
    Route::get('mapIp', [AppController::class, 'mapIp']);

    //Record DiscountCode copy event 
    Route::get('recordDiscountCodeCopyEvent', [ExtensionController::class, 'recordCopyDiscountCodeEvent']);
    
    //Send events information to Alme backend.
    Route::post('sendEvent', [AppController::class, 'sendEventToAlme']);
    
    //Send user submit contact to Alme also save it in backend.
    Route::post('submitContact', [AppController::class, 'submitContact']);

    //Decides if sale notification popup should be shown to user.
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

//Just for testing.
Route::post('mapCheckout', [AppController::class, 'mapCheckout']);
Route::get('mapCustomer', [AppController::class, 'mapCustomer']);

//Shopify Installation Routes.
//When Shopify app is installed by merchant these two routes are in action.
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
Route::get('checkStoreInstallAndScript', [HomeController::class, 'checkInstallAndScript']);
Route::get('checkPriceRulesForShops', [HomeController::class, 'testPriceRules']);
