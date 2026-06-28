<?php

use Illuminate\Support\Facades\Route;
use RoyalPanel\Http\Middleware\AdminAuthenticate;
use RoyalPanel\Http\Middleware\RequireTwoFactorAuthentication;
use RoyalPanel\RoyalAtelier\Controllers\ExtensionAdminController;

Route::group([
    'prefix' => 'admin/extensions/rx',
    'middleware' => ['web', 'auth.session', RequireTwoFactorAuthentication::class, AdminAuthenticate::class, 'security'],
    'as' => 'rxadmin.extensions.',
], function () {
    Route::get('/', [ExtensionAdminController::class, 'index'])->name('index');
    Route::get('settings', [ExtensionAdminController::class, 'settings'])->name('settings');
    Route::post('settings', [ExtensionAdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('install', [ExtensionAdminController::class, 'install'])->name('install');
    Route::get('{id}', [ExtensionAdminController::class, 'show'])->name('show');
    Route::delete('{id}', [ExtensionAdminController::class, 'uninstall'])->name('uninstall');
    Route::post('{id}/toggle', [ExtensionAdminController::class, 'toggle'])->name('toggle');
});
