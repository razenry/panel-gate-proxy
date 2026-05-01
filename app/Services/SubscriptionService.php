<?php

namespace App\Services;

use App\Jobs\DeleteServerJob;
use App\Jobs\ProvisionServerJob;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function handleStatusChange(Subscription $subscription, string $oldStatus): void
    {
        $newStatus = $subscription->status;
        Log::info('[Subscription] Status changed', [
            'id' => $subscription->id,
            'old' => $oldStatus,
            'new' => $newStatus
        ]);

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
        Log::info('[Subscription] Suspending all servers', ['subscription_id' => $subscription->id]);
        foreach ($subscription->servers as $server) {
            if ($server->status === 'suspended') continue;
            
            app(\App\Services\ServerService::class)->suspend($server);
        }
    }

    public function terminateServers(Subscription $subscription): void
    {
        Log::warning('[Subscription] Terminating all servers and data', ['subscription_id' => $subscription->id]);
        foreach ($subscription->servers as $server) {
            app(\App\Services\ServerService::class)->delete($server);
        }
        
        // We keep the subscription record but marked as terminated if it's already updated, 
        // or we can delete it if that's the preferred way. The PaymenterService currently 
        // updates status to 'terminated' then calls this.
        // To strictly follow "Set status to terminated", we won't delete the subscription record itself here.
        Log::info('[Subscription] Termination complete', ['subscription_id' => $subscription->id]);
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
