<?php

namespace App\Services;

use App\Jobs\DeleteServerJob;
use App\Jobs\ProvisionServerJob;
use App\Models\Subscription;

class SubscriptionService
{
    /**
     * Handle status change of a subscription.
     */
    public function handleStatusChange(Subscription $subscription, string $oldStatus): void
    {
        $newStatus = $subscription->status;

        if ($newStatus === 'active') {
            $this->activateServers($subscription);
        } elseif ($newStatus === 'suspended') {
            $this->suspendServers($subscription);
        } elseif ($newStatus === 'terminated') {
            $this->terminateServers($subscription);
        }
    }

    public function restoreServers(Subscription $subscription): void
    {
        foreach ($subscription->servers as $server) {
            if ($server->status === 'suspended') {
                $server->update(['status' => 'pending']);
                ProvisionServerJob::dispatch($server);
            }
        }
    }

    public function activateServers(Subscription $subscription): void
    {
        foreach ($subscription->servers as $server) {
            $server->update(['status' => 'pending']);
            ProvisionServerJob::dispatch($server);
        }
    }

    public function suspendServers(Subscription $subscription): void
    {
        foreach ($subscription->servers as $server) {
            $proxyId = $server->proxy_id ?? "kafka_{$server->identifier}";
            if ($server->node_id && $server->node) {
                DeleteServerJob::dispatch($server->node, $proxyId);
            }
            $server->update(['status' => 'suspended']);
        }
    }

    public function terminateServers(Subscription $subscription): void
    {
        foreach ($subscription->servers as $server) {
            $proxyId = $server->proxy_id ?? "kafka_{$server->identifier}";
            if ($server->node_id && $server->node) {
                DeleteServerJob::dispatch($server->node, $proxyId);
            }
            $server->delete();
        }
        $subscription->delete();
    }
    
    public function createSubscription(array $data): Subscription
    {
        $user = \App\Models\User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
            ]
        );

        $plan = \App\Models\Plan::where('name', $data['plan_name'])->firstOrFail();

        return Subscription::create([
            'user_id' => $user->id,
            'external_id' => $data['external_id'] ?? null,
            'plan_name' => $plan->name,
            'max_server' => $plan->max_server,
            'status' => $data['status'],
            'expired_at' => !empty($data['expired_at']) ? \Carbon\CarbonImmutable::parse($data['expired_at']) : null,
        ]);
    }
}
