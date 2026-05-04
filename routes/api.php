<?php

use App\Http\Controllers\Api\Admin\NodeController;
use App\Http\Controllers\Api\Client\ServerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['api_key']);


// External Integration API for Paymenter
Route::middleware(['external_api'])->prefix('external')->name('external.')->group(function () {
    Route::post('users/sync', [\App\Http\Controllers\Api\External\UserController::class, 'sync']);
    Route::post('subscriptions/sync', [\App\Http\Controllers\Api\External\SubscriptionController::class, 'sync']);
    Route::post('subscriptions/activate', [\App\Http\Controllers\Api\External\SubscriptionController::class, 'activate']);
    Route::post('subscriptions/suspend', [\App\Http\Controllers\Api\External\SubscriptionController::class, 'suspend']);
    Route::post('subscriptions/terminate', [\App\Http\Controllers\Api\External\SubscriptionController::class, 'terminate']);
});

Route::middleware(['api_key'])->group(function () {

    // Client endpoints
    Route::prefix('client')->name('api.client.')->group(function () {
        Route::apiResource('servers', ServerController::class)->except(['update']);

        // Account & Security
        Route::prefix('account')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Client\AccountController::class, 'index']);
            Route::put('email', [\App\Http\Controllers\Api\Client\AccountController::class, 'updateEmail']);
            Route::put('password', [\App\Http\Controllers\Api\Client\AccountController::class, 'updatePassword']);

            Route::get('two-factor', [\App\Http\Controllers\Api\Client\TwoFactorController::class, 'index']);
            Route::post('two-factor', [\App\Http\Controllers\Api\Client\TwoFactorController::class, 'store']);
            Route::delete('two-factor', [\App\Http\Controllers\Api\Client\TwoFactorController::class, 'destroy']);

            Route::get('api-keys', [\App\Http\Controllers\Api\Client\ApiKeyController::class, 'index']);
            Route::post('api-keys', [\App\Http\Controllers\Api\Client\ApiKeyController::class, 'store']);
            Route::delete('api-keys/{id}', [\App\Http\Controllers\Api\Client\ApiKeyController::class, 'destroy']);

            Route::get('ssh-keys', [\App\Http\Controllers\Api\Client\SSHKeyController::class, 'index']);
            Route::post('ssh-keys', [\App\Http\Controllers\Api\Client\SSHKeyController::class, 'store']);
            Route::post('ssh-keys/remove', [\App\Http\Controllers\Api\Client\SSHKeyController::class, 'remove']);

            Route::get('activity', [\App\Http\Controllers\Api\Client\ActivityController::class, 'index']);
        });
    });


    // Admin endpoints
    Route::middleware(['admin'])->prefix('admin')->name('api.admin.')->group(function () {
        Route::apiResource('nodes', NodeController::class);
        
        // Expose generic tools for Paymenter
        Route::apiResource('subscriptions', \App\Http\Controllers\Api\Admin\SubscriptionController::class);
        Route::apiResource('servers', \App\Http\Controllers\Api\Admin\ServerController::class)->except(['update']);
        Route::post('servers/{server}/redeploy', [\App\Http\Controllers\Api\Admin\ServerController::class, 'redeploy'])->name('servers.redeploy');

        
        // Minecraft Management API
        Route::prefix('minecraft/servers/{server}')->name('minecraft.')->group(function () {
            Route::get('plugins', [\App\Http\Controllers\Api\Minecraft\MinecraftController::class, 'listPlugins']);
            Route::post('plugins', [\App\Http\Controllers\Api\Minecraft\MinecraftController::class, 'installPlugin']);
            Route::post('config', [\App\Http\Controllers\Api\Minecraft\MinecraftController::class, 'updateConfig']);
            Route::get('connection', [\App\Http\Controllers\Api\Minecraft\MinecraftController::class, 'getConnectionInfo']);
        });
    });
});
