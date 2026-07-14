<?php

/**
 * Authentication
 */
Route::get('login', 'Auth\LoginController@show');
Route::post('login', 'Auth\LoginController@login');
Route::get('logout', 'Auth\LoginController@logout')->name('auth.logout');

Route::group(['middleware' => ['registration', 'guest']], function () {
    Route::get('register', 'Auth\RegisterController@show');
    Route::post('register', 'Auth\RegisterController@register');
});

Route::emailVerification();

Route::group(['middleware' => ['password-reset', 'guest']], function () {
    Route::resetPassword();
});

/**
 * Two-Factor Authentication
 */
Route::group(['middleware' => 'two-factor'], function () {
    Route::get('auth/two-factor-authentication', 'Auth\TwoFactorTokenController@show')->name('auth.token');
    Route::post('auth/two-factor-authentication', 'Auth\TwoFactorTokenController@update')->name('auth.token.validate');
});

/**
 * Social Login
 */
Route::get('auth/{provider}/login', 'Auth\SocialAuthController@redirectToProvider')->name('social.login');
Route::get('auth/{provider}/callback', 'Auth\SocialAuthController@handleProviderCallback');

/**
 * Impersonate Routes
 */
    Route::group(['middleware' => 'auth'], function () {
        Route::impersonate();
    });

    Route::group(['middleware' => ['auth', 'verified']], function () {

    /**
     * Dashboard
     */
    Route::get('/', 'DashboardController@index')->name('dashboard');

    Route::resource('contacts', ContactController::class)->names([
        'index' => 'contact',  // Now 'contact' is mapped to contacts.index
    ]);

   

    /**
     * User Profile
     */
    Route::group(['prefix' => 'profile', 'namespace' => 'Profile'], function () {
        Route::get('/', 'ProfileController@show')->name('profile');
        Route::get('activity', 'ActivityController@show')->name('profile.activity');
        Route::put('details', 'DetailsController@update')->name('profile.update.details');

        Route::post('avatar', 'AvatarController@update')->name('profile.update.avatar');
        Route::post('avatar/external', 'AvatarController@updateExternal')
            ->name('profile.update.avatar-external');

        Route::put('login-details', 'LoginDetailsController@update')
            ->name('profile.update.login-details');

        Route::get('sessions', 'SessionsController@index')
            ->name('profile.sessions')
            ->middleware('session.database');

        Route::delete('sessions/{session}/invalidate', 'SessionsController@destroy')
            ->name('profile.sessions.invalidate')
            ->middleware('session.database');
    });

    /**
     * Two-Factor Authentication Setup
     */
    Route::group(['middleware' => 'two-factor'], function () {
        Route::post('two-factor/enable', 'TwoFactorController@enable')->name('two-factor.enable');

        Route::get('two-factor/verification', 'TwoFactorController@verification')
            ->name('two-factor.verification')
            ->middleware('verify-2fa-code');

        Route::post('two-factor/verify', 'TwoFactorController@verify')
            ->name('two-factor.verify')
            ->middleware('verify-2fa-code');

        Route::post('two-factor/disable', 'TwoFactorController@disable')->name('two-factor.disable');
    });

    /**
     * User Management
     */
    Route::resource('users', 'Users\UsersController')
        ->except('update')->middleware('permission:users.manage');

    Route::group(['prefix' => 'users/{user}', 'middleware' => 'permission:users.manage'], function () {
        Route::put('update/details', 'Users\DetailsController@update')->name('users.update.details');
        Route::put('update/login-details', 'Users\LoginDetailsController@update')
            ->name('users.update.login-details');

        Route::post('update/avatar', 'Users\AvatarController@update')->name('user.update.avatar');
        Route::post('update/avatar/external', 'Users\AvatarController@updateExternal')
            ->name('user.update.avatar.external');

        Route::get('sessions', 'Users\SessionsController@index')
            ->name('user.sessions')->middleware('session.database');

        Route::delete('sessions/{session}/invalidate', 'Users\SessionsController@destroy')
            ->name('user.sessions.invalidate')->middleware('session.database');

        Route::post('two-factor/enable', 'TwoFactorController@enable')->name('user.two-factor.enable');
        Route::post('two-factor/disable', 'TwoFactorController@disable')->name('user.two-factor.disable');
    });

    /**
     * Old Order Management
     */
    Route::get('oldorders', 'OldOrderController@index')->name('oldorders');
    Route::get('oldorder/show/{id}', 'OldOrderController@show')->name('oldorder.show');
    /**
     * Order Management
     */
    Route::get('orders', 'OrderController@index')->name('orders');
    Route::get('order/show/{id}', 'OrderController@show')->name('order.show');

    /*Route::get('products', 'ProductController@index')->name('products');
    Route::get('product/show/{id}', 'ProductController@show')->name('product.show');*/
    //->middleware('permission:order.show');
    //Route::resource('orders', 'Order\OrderController');
    
    Route::get('update-sheet', 'GoogleSheetController@updateSheet')->name('updateSheet');

    Route::get('order_item_logs', 'OrderItemLogsController@index')->name('order.item.logs');
    Route::get('order_item_logsnew', 'OrderItemLogsNewController@index')->name('order.item.logsnew');

    Route::get('addons', 'AddonsController@index')->name('addons');
    Route::get('orderreportstatus', 'OrderReportStatusController@index')->name('reports.orderstatus');
    Route::get('/orderstatus/export', 'OrderReportStatusController@export')->name('orderstatus.export');
    Route::get('/orderstatus/exportpdf', 'OrderReportStatusController@exportPdf')->name('orderstatus.exportPdf');
    Route::get('trackingordermsrmt', 'TrackingOrderMsrmtController@index')->name('reports.trackingordermsrmt'); 
    
    Route::get('trackingorder', 'TrackingOrderController@index')->name('reports.trackingorder');
    Route::get('newitemlogs', 'NewItemLogsController@index')->name('reports.newitemlogs');/* Working for New Report */
    Route::get('newitemlogs/history', 'NewItemLogsController@history')->name('reports.newitemlogs.history'); 

    Route::get('newitemlogstracker', 'NewItemLogsTrackerController@index')->name('reports.newitemlogstracker');
    Route::get('/newitemlogstracker/export', 'NewItemLogsTrackerController@export')->name('newitemlogstracker.export');
    Route::get('/newitemlogstracker/exportpdf', 'NewItemLogsTrackerController@exportPdf')->name('newitemlogstracker.exportPdf');

    
    Route::get('printmeasurement/{id}', 'PrintMeasurementController@show')->name('printmeasurement.show');
    
    /*Route::get('order_item_holdbycs', 'OrderItemHoldByCsController@index')->name('order.item.holdbycs');
    Route::post('order_item_holdbycs', 'OrderItemHoldByCsController@updateItemDetails')->name('order.item.update');*/

    Route::get('order_item_comments', 'OrderItemCommentsController@index')->name('order.item.comments');
    Route::post('order_item_comments', 'OrderItemCommentsController@updateItemComment')->name('order.item.updatecomment');
    Route::delete('order_item_comments/{id}', 'OrderItemCommentsController@deleteItemComment')->name('order.item.deletecomment');

    Route::get('order_item_imagereplace', 'OrderItemImageReplaceController@index')->name('order.item.imgreplace');
    Route::post('order_item_imagereplace', 'OrderItemImageReplaceController@updateItemImageReplace')->name('order.item.imagereplace');

    Route::get('order_item_updateqty', 'OrderItemUpdateQtyController@index')->name('order.item.updateqty');
    Route::post('order_item_updateqty', 'OrderItemUpdateQtyController@updateItemQty')->name('order.item.updateqty');

    Route::get('order_item_modifymeasurement', 'OrderItemModifyMeasurementController@index')->name('order.item.measurementform');
    Route::post('order_item_modifymeasurement', 'OrderItemModifyMeasurementController@updateMeasurementDetails')->name('order.item.addmeasurement');

    Route::get('skuorderlist', 'SkuOrderListController@index')->name('skuorderlist');

    Route::get('order_report_remarks', 'OrderDateWiseReportRemarkController@index')->name('order.report.remarks');
    Route::post('order_report_remarks', 'OrderDateWiseReportRemarkController@updateReportRemark')->name('order.report.updateremarks');


    Route::post('/hold-to-unhold/update', 'HoldToUnholdController@updateIndicate')->name('hold.unhold.update'); 
    Route::middleware('permission:fedexftb')->group(function () {
        Route::get('fedex/shipment/create', 'FedExBatchController@index')->name('fedex.shipment.create');
        Route::get('fedex/batch/rows', 'FedExBatchController@rows')->name('fedex.batch.rows');
        Route::post('fedex/batch/process', 'FedExBatchController@processRow')->name('fedex.batch.process');
        Route::get('fedex/invoice/view', 'FedExBatchController@viewInvoice')->name('fedex.invoice.view');
        Route::get('fedex/batch/tally-export', 'FedExBatchController@tallyExport')->name('fedex.batch.tally.export');
        Route::get('fedex/print/label', 'FedExBatchController@printLabel')->name('fedex.print.label');
        Route::get('fedex/print/invoice', 'FedExBatchController@printInvoice')->name('fedex.print.invoice');
        Route::get('fedex/label/download', 'FedExShipmentController@downloadLabel')->name('fedex.label.download');
        Route::get('fedex/invoice-lookup', 'FedExShipmentController@invoiceLookup')->name('fedex.invoice.lookup');
    }); 
    /*Route::get('order_item_modifymeasurement', 'StandardMeasurementController@index')->name('order.item.measurementform');
    Route::post('order_item_modifymeasurement', 'StandardMeasurementController@updateMeasurementDetails')->name('order.item.addmeasurement');

    Route::post('/standard-measurements', [StandardMeasurementController::class, 'store']);*/

    /**
     * Roles & Permissions
     */
    Route::group(['namespace' => 'Authorization'], function () {
        Route::resource('roles', 'RolesController')->except('show')->middleware('permission:roles.manage');

        Route::post('permissions/save', 'RolePermissionsController@update')
            ->name('permissions.save')
            ->middleware('permission:permissions.manage');

        Route::resource('permissions', 'PermissionsController')->middleware('permission:permissions.manage');
    });

    /**
     * Settings
     */
    Route::get('settings', 'SettingsController@general')->name('settings.general')
        ->middleware('permission:settings.general');

    Route::post('settings/general', 'SettingsController@update')->name('settings.general.update')
        ->middleware('permission:settings.general');

    Route::get('settings/auth', 'SettingsController@auth')->name('settings.auth')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth', 'SettingsController@update')->name('settings.auth.update')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth/2fa/enable', 'SettingsController@enableTwoFactor')
        ->name('settings.auth.2fa.enable')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth/2fa/disable', 'SettingsController@disableTwoFactor')
        ->name('settings.auth.2fa.disable')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth/registration/captcha/enable', 'SettingsController@enableCaptcha')
        ->name('settings.registration.captcha.enable')
        ->middleware('permission:settings.auth');

    Route::post('settings/auth/registration/captcha/disable', 'SettingsController@disableCaptcha')
        ->name('settings.registration.captcha.disable')
        ->middleware('permission:settings.auth');

    Route::get('settings/notifications', 'SettingsController@notifications')
        ->name('settings.notifications')
        ->middleware('permission:settings.notifications');

    Route::post('settings/notifications', 'SettingsController@update')
        ->name('settings.notifications.update')
        ->middleware('permission:settings.notifications');

    /**
     * Activity Log
     */
    Route::get('activity', 'ActivityController@index')->name('activity.index')
        ->middleware('permission:users.activity');

    Route::get('activity/user/{user}/log', 'Users\ActivityController@index')->name('activity.user')
        ->middleware('permission:users.activity');


     /**
     * Activity Log
     */  
    Route::group(['middleware' => ['auth', 'permission:addons']], function () {
        Route::resource('addons', AddonsController::class);
    });
    Route::group(['middleware' => ['auth', 'permission:orders']], function () {
        Route::resource('orders', OrderController::class)->names([
            'index' => 'orders',
        ]);
    });
    Route::group(['middleware' => ['auth', 'permission:oldorders']], function () {
        Route::resource('oldorders', OldOrderController::class)->names([
            'index' => 'oldorders',
        ]);
    });
}); 

/**
 * Installation
 */
Route::group(['prefix' => 'install'], function () {
    Route::get('/', 'InstallController@index')->name('install.start');
    Route::get('requirements', 'InstallController@requirements')->name('install.requirements');
    Route::get('permissions', 'InstallController@permissions')->name('install.permissions');
    Route::get('database', 'InstallController@databaseInfo')->name('install.database');
    Route::get('start-installation', 'InstallController@installation')->name('install.installation');
    Route::post('start-installation', 'InstallController@installation')->name('install.installation');
    Route::post('install-app', 'InstallController@install')->name('install.install');
    Route::get('complete', 'InstallController@complete')->name('install.complete');
    Route::get('error', 'InstallController@error')->name('install.error');
});
