<?php

use App\Http\Controllers\Api\Admin\LocationController;
use App\Http\Controllers\Api\Admin\NodeController;
use App\Http\Controllers\Api\Client\AccountController;
use App\Http\Controllers\Api\Client\ActivityController;
use App\Http\Controllers\Api\Client\ApiKeyController;
use App\Http\Controllers\Api\Client\ServerController;
use App\Http\Controllers\Api\Client\SSHKeyController;
use App\Http\Controllers\Api\Client\TwoFactorController;
use App\Http\Controllers\Api\External\SubscriptionController;
use App\Http\Controllers\Api\External\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['api_key']);

// External Integration API for Paymenter
Route::middleware(['external_api'])->prefix('external')->name('external.')->group(function () {
    Route::post('users/sync', [UserController::class, 'sync']);
    Route::post('subscriptions/sync', [SubscriptionController::class, 'sync']);
    Route::post('subscriptions/activate', [SubscriptionController::class, 'activate']);
    Route::post('subscriptions/suspend', [SubscriptionController::class, 'suspend']);
    Route::post('subscriptions/terminate', [SubscriptionController::class, 'terminate']);
});

Route::middleware(['api_key'])->group(function () {

    // Client endpoints
    Route::prefix('client')->name('api.client.')->group(function () {
        Route::apiResource('servers', ServerController::class)->except(['update']);

        // Account & Security
        Route::prefix('account')->group(function () {
            Route::get('/', [AccountController::class, 'index']);
            Route::put('email', [AccountController::class, 'updateEmail']);
            Route::put('password', [AccountController::class, 'updatePassword']);

            Route::get('two-factor', [TwoFactorController::class, 'index']);
            Route::post('two-factor', [TwoFactorController::class, 'store']);
            Route::delete('two-factor', [TwoFactorController::class, 'destroy']);

            Route::get('api-keys', [ApiKeyController::class, 'index']);
            Route::post('api-keys', [ApiKeyController::class, 'store']);
            Route::delete('api-keys/{id}', [ApiKeyController::class, 'destroy']);

            Route::get('ssh-keys', [SSHKeyController::class, 'index']);
            Route::post('ssh-keys', [SSHKeyController::class, 'store']);
            Route::post('ssh-keys/remove', [SSHKeyController::class, 'remove']);

            Route::get('activity', [ActivityController::class, 'index']);
        });
    });

    // Admin endpoints
    Route::middleware(['admin'])->prefix('admin')->name('api.admin.')->group(function () {
        Route::apiResource('nodes', NodeController::class);

        // Expose generic tools for Paymenter
        Route::apiResource('locations', LocationController::class);
        Route::apiResource('subscriptions', App\Http\Controllers\Api\Admin\SubscriptionController::class);
        Route::apiResource('servers', App\Http\Controllers\Api\Admin\ServerController::class)->except(['update']);
        Route::post('servers/{server}/redeploy', [App\Http\Controllers\Api\Admin\ServerController::class, 'redeploy'])->name('servers.redeploy');

        /*
        // Minecraft Management API
        Route::prefix('minecraft/servers/{server}')->name('minecraft.')->group(function () {
            Route::get('plugins', [\App\Http\Controllers\Api\Minecraft\MinecraftController::class, 'listPlugins']);
            Route::post('plugins', [\App\Http\Controllers\Api\Minecraft\MinecraftController::class, 'installPlugin']);
            Route::post('config', [\App\Http\Controllers\Api\Minecraft\MinecraftController::class, 'updateConfig']);
            Route::get('connection', [\App\Http\Controllers\Api\Minecraft\MinecraftController::class, 'getConnectionInfo']);
        });
        */

    });
});
