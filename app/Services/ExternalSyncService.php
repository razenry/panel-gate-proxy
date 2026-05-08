<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Server;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ExternalSyncService
{
    /**
     * Synchronize a user from an external system.
     */
    public function syncUser(string $email, string $name): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(Str::random(16)), // Only set on creation usually, updateOrCreate handles it
            ]
        );
    }

    /**
     * Synchronize a subscription from an external system.
     */
    public function syncSubscription(array $data): Subscription
    {
        $user = User::where('email', $data['email'])->firstOrFail();

        // Try to resolve internal plan details from plan_id
        $planName = $data['plan_name'] ?? 'External Plan';
        $maxServer = $data['max_server'] ?? 1;

        if (!empty($data['plan_id'])) {
            $plan = \App\Models\Plan::where('plan_id', $data['plan_id'])->first();
            if ($plan) {
                $planName = $plan->name;
                $maxServer = $plan->max_server;
            }
        }

        return Subscription::updateOrCreate(
            ['external_id' => $data['external_id']],
            [
                'user_id' => $user->id,
                'plan_id' => $data['plan_id'] ?? null,
                'plan_name' => $planName,
                'max_server' => $maxServer,
                'status' => $data['status'] ?? 'active',
                'expired_at' => $data['expired_at'] ?? null,
            ]
        );
    }

    /**
     * Suspend a subscription.
     */
    public function suspendSubscription(string $externalId): bool
    {
        $subscription = Subscription::where('external_id', $externalId)->firstOrFail();
        
        DB::transaction(function () use ($subscription) {
            $subscription->update(['status' => 'suspended']);

            // "Remove proxies only" - logic depends on how proxies are stored.
            // Usually clearing proxy_id and related fields.
            $subscription->servers()->update([
                'proxy_id' => null,
                'status' => 'suspended',
            ]);
        });

        return true;
    }

    /**
     * Activate a subscription.
     */
    public function activateSubscription(string $externalId): bool
    {
        $subscription = Subscription::where('external_id', $externalId)->firstOrFail();
        $subscription->update(['status' => 'active']);
        
        // Re-activating servers usually requires re-provisioning if proxies were removed.
        // For now, just update status.
        $subscription->servers()->update(['status' => 'active']);

        return true;
    }

    /**
     * Terminate a subscription and clean up everything.
     */
    public function terminateSubscription(string $externalId): bool
    {
        $subscription = Subscription::where('external_id', $externalId)->firstOrFail();

        DB::transaction(function () use ($subscription) {
            // Delete all related servers
            // We might need to call specific cleanup logic for each server (DNS, etc)
            foreach ($subscription->servers as $server) {
                $this->cleanupServer($server);
            }

            $subscription->delete();
        });

        return true;
    }

    /**
     * Internal helper to cleanup server resources.
     */
    protected function cleanupServer(Server $server): void
    {
        // Delete DNS records if any
        // Delete proxy configurations
        // ... implementation specific ...
        
        $server->delete();
    }
}
