<?php

use App\Http\Controllers\Api\Admin\NodeController;
use App\Http\Controllers\Api\Client\ServerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['ip_whitelist', 'api_key']);

Route::middleware(['ip_whitelist', 'api_key'])->group(function () {
    // Client endpoints
    Route::name('api.client.')->group(function () {
        Route::apiResource('servers', ServerController::class)->except(['update']);
    });

    // External Integration API (Generic) - Moved outside admin prefix
    Route::prefix('external')->name('external.')->group(function () {
        Route::post('users/sync', [\App\Http\Controllers\Api\External\UserSyncController::class, 'sync']);
        Route::post('subscriptions/sync', [\App\Http\Controllers\Api\External\SubscriptionSyncController::class, 'sync']);
        Route::post('subscriptions/{external_id}/activate', [\App\Http\Controllers\Api\External\SubscriptionSyncController::class, 'activate']);
        Route::post('subscriptions/{external_id}/suspend', [\App\Http\Controllers\Api\External\SubscriptionSyncController::class, 'suspend']);
        Route::post('subscriptions/{external_id}/terminate', [\App\Http\Controllers\Api\External\SubscriptionSyncController::class, 'terminate']);
    });

    // Admin endpoints
    Route::middleware(['admin'])->prefix('admin')->name('api.admin.')->group(function () {
        Route::apiResource('nodes', NodeController::class);
        Route::apiResource('locations', \App\Http\Controllers\Api\Admin\LocationController::class);
        
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
