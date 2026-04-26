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

    // Admin endpoints
    Route::middleware(['admin'])->prefix('admin')->name('api.admin.')->group(function () {
        Route::apiResource('nodes', NodeController::class);
        
        // Expose generic tools for Paymenter
        Route::apiResource('subscriptions', \App\Http\Controllers\Api\Admin\SubscriptionController::class);
        Route::apiResource('servers', \App\Http\Controllers\Api\Admin\ServerController::class)->except(['update']);
        Route::post('servers/{server}/redeploy', [\App\Http\Controllers\Api\Admin\ServerController::class, 'redeploy'])->name('servers.redeploy');
    });
});
