<?php

use App\Services\PaymenterService;
use App\Models\Subscription;
use App\Models\User;

$service = app(PaymenterService::class);

echo "--- Test 1: New Subscription ---\n";
try {
    $sub = $service->handleHook('created', [
        'email' => 'test@raznar.com',
        'name' => 'Test User',
        'external_id' => 'PAY-12345',
        'plan_name' => 'Starter',
        'expired_at' => now()->addMonth()->toIso8601String(),
    ]);
    echo "Created Sub ID: {$sub->id}, Status: {$sub->status}, External ID: {$sub->external_id}\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n--- Test 2: Suspend Subscription ---\n";
try {
    $sub = $service->handleHook('suspended', [
        'external_id' => 'PAY-12345',
    ]);
    echo "Suspended Sub ID: {$sub->id}, Status: {$sub->status}\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n--- Test 3: Re-activate Subscription ---\n";
try {
    $sub = $service->handleHook('activated', [
        'external_id' => 'PAY-12345',
        'email' => 'test@raznar.com',
        'plan_name' => 'Starter',
    ]);
    echo "Activated Sub ID: {$sub->id}, Status: {$sub->status}\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n--- Test 4: Terminate Subscription ---\n";
try {
    $sub = $service->handleHook('terminated', [
        'external_id' => 'PAY-12345',
    ]);
    echo "Terminated Sub ID: {$sub->id}, Status: {$sub->status}\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
