<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

class ExternalIntegrationService
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected ServerService $serverService
    ) {}

    /**
     * Synchronize a user from external system to the panel.
     */
    public function syncUser(array $data): User
    {
        return User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'] ?? explode('@', $data['email'])[0],
                'password' => Hash::make(Str::random(16)),
            ]
        );
    }

    /**
     * Synchronize a subscription safely.
     */
    public function syncSubscription(array $data): Subscription
    {
        $user = $this->syncUser($data);
        $plan = Plan::where('name', $data['plan_name'])->firstOrFail();

        $subscription = Subscription::where('external_id', $data['external_id'])->first();

        $status = $data['status'] ?? 'pending';

        if (!$subscription) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'external_id' => $data['external_id'],
                'plan_name' => $plan->name,
                'max_server' => $data['max_server'] ?? $plan->max_server,
                'status' => $status,
                'expired_at' => !empty($data['expired_at']) ? CarbonImmutable::parse($data['expired_at']) : null,
            ]);

            Log::info('[External Integration] New subscription created', ['id' => $subscription->id, 'external_id' => $data['external_id']]);
        } else {
            $oldStatus = $subscription->status;
            
            $updates = [
                'status' => $status,
                'max_server' => $data['max_server'] ?? $subscription->max_server,
            ];
            
            if (!empty($data['expired_at'])) {
                $updates['expired_at'] = CarbonImmutable::parse($data['expired_at']);
            }
            
            $subscription->update($updates);
            
            if ($oldStatus !== $status) {
                Log::info("[External Integration] Subscription status changed from {$oldStatus} to {$status}", ['id' => $subscription->id]);
                $this->subscriptionService->handleStatusChange($subscription, $oldStatus);
            }
        }

        return $subscription;
    }

    /**
     * Activate a subscription and its servers.
     */
    public function activateSubscription(string $externalId, array $data): Subscription
    {
        $data['external_id'] = $externalId;
        $data['status'] = 'active';
        
        return $this->syncSubscription($data);
    }

    /**
     * Suspend an active subscription and its servers.
     */
    public function suspendSubscription(string $externalId): Subscription
    {
        $subscription = Subscription::where('external_id', $externalId)->firstOrFail();
        $oldStatus = $subscription->status;

        $subscription->update(['status' => 'suspended']);

        if ($oldStatus !== 'suspended') {
            Log::info("[External Integration] Subscription suspended", ['id' => $subscription->id]);
            $this->subscriptionService->handleStatusChange($subscription, $oldStatus);
        }

        return $subscription;
    }

    /**
     * Terminate a subscription and delete related servers/proxies.
     */
    public function terminateSubscription(string $externalId): Subscription
    {
        $subscription = Subscription::where('external_id', $externalId)->firstOrFail();
        
        Log::warning('[External Integration] Terminating subscription', ['id' => $subscription->id]);
        
        $subscription->update(['status' => 'terminated']);
        $this->subscriptionService->terminateServers($subscription);

        return $subscription;
    }
}
