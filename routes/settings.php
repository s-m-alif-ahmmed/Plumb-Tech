<?php

//use App\Http\Controllers\Web\Backend\Settings\DynamicPageController;
//use App\Http\Controllers\Web\Backend\Settings\ProfileController;
//use App\Http\Controllers\Web\Backend\Settings\SystemSettingController;
//
//Route::middleware(['auth:web','admin'])->group(function () {
//    Route::controller(SystemSettingController::class)->group(function () {
//        Route::get('setting-system', 'index')->name('setting.system.index');
//        Route::post('setting-update','update')->name('setting.system.update');
//        Route::get('setting-configuration', 'configuration')->name('setting.configuration.index');
//        // Mail Settings
//        Route::post('setting-configuration-mail','mailSettingUpdate')->name('setting.configuration.mail');
//        // Payments Settings
//        Route::post('setting-configuration-payment','stripeSettingUpdate')->name('setting.configuration.payment');
//        // Social App Settings
//        Route::post('setting-configuration-social','socialAppUpdate')->name('setting.configuration.social');
//    });
//
//    Route::controller(ProfileController::class)->name('setting.profile.')->group(function () {
//        Route::get('setting-profile','index')->name('index');
//        Route::post('setting-profile', 'update')->name('update');
//        Route::post('setting-profile-password', 'UpdatePassword')->name('password');
//    });
//});
//
////Route for DynamicPageController
//Route::resource('/dynamic-page', DynamicPageController::class);
//Route::post('/dynamic-page/status/{id}', [DynamicPageController::class, 'status'])->name('dynamic.page.status');
