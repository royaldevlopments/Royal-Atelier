<?php

use Illuminate\Support\Facades\Route;
use RoyalPanel\Http\Middleware\AdminAuthenticate;
use RoyalPanel\Http\Middleware\RequireTwoFactorAuthentication;
use RoyalPanel\RoyalAtelier\Models\RxExtension;

/*
|--------------------------------------------------------------------------
| Atelier Extension Admin Routes
|--------------------------------------------------------------------------
|
| Dynamically register admin routes for each installed extension.
| Endpoint: /admin/extensions/rx/{identifier}
|
*/

Route::group([
    'prefix' => 'admin/extensions/rx',
    'middleware' => ['web', 'auth.session', RequireTwoFactorAuthentication::class, AdminAuthenticate::class, 'security'],
    'as' => 'rxadmin.extensions.',
], function () {
    $extensions = RxExtension::where('installed', true)->get();

    foreach ($extensions as $ext) {
        $id = $ext->extension_id;
        $controllerName = $id . 'ExtensionController';
        $controllerClass = "RoyalPanel\\Http\\Controllers\\Admin\\Extensions\\{$id}\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            continue;
        }

        Route::group(['prefix' => $id], function () use ($controllerClass, $id) {
            Route::get('/', [$controllerClass, 'index'])->name("{$id}.index");
            Route::post('/', [$controllerClass, 'post'])->name("{$id}.post");
            Route::patch('/', [$controllerClass, 'update'])->name("{$id}.update");
            Route::put('/', [$controllerClass, 'put'])->name("{$id}.put");
            Route::delete('/{target}/{sub}', [$controllerClass, 'delete'])->name("{$id}.delete");
        });
    }
});