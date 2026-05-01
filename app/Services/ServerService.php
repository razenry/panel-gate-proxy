<?php

namespace App\Services;

use App\Jobs\DeleteServerJob;
use App\Jobs\ProvisionServerJob;
use App\Models\Server;
use App\Models\Setting;
use App\Models\User;
use App\Models\Node;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ServerService
{
    /**
     * Create a new server for a user.
     */
    public function create(User $user, array $data): Server
    {
        $subscription = $user->subscriptions()
            ->where('id', $data['subscription_id'])
            ->first();

        if (! $subscription || ! $subscription->isActive()) {
            throw ValidationException::withMessages(['subscription' => 'No active or valid subscription found.']);
        }

        // Check limits
        $currentCount = $user->servers()->where('subscription_id', $subscription->id)->count();
        if ($currentCount >= $subscription->max_server) {
            throw ValidationException::withMessages(['subscription' => 'Server limit reached for this subscription.']);
        }

        $node = Node::findOrFail($data['node_id']);

        return DB::transaction(function () use ($user, $data, $subscription, $node) {
            $provisioningType = ($node->type === 'pterodactyl') ? 'pterodactyl' : 'proxy';

            $serverData = [
                'user_id' => $user->id,
                'label' => $data['label'],
                'identifier' => strtolower($data['identifier']),
                'src_ip' => $data['src_ip'] ?? '0.0.0.0',
                'src_port' => $data['src_port'] ?? 0,
                'node_id' => $node->id,
                'subscription_id' => $subscription->id,
                'status' => 'pending',
                'provisioning_type' => $provisioningType,
            ];

            if ($provisioningType === 'proxy') {
                $serverData['dest_port'] = $this->generateRandomPort($node->id);
            }

            $server = Server::create($serverData);

            ProvisionServerJob::dispatch($server);

            return $server;
        });
    }

    /**
     * Delete a server.
     */
    public function delete(Server $server): void
    {
        Log::info('[Server] Deleting server', ['id' => $server->id, 'type' => $server->provisioning_type]);

        if ($server->provisioning_type === 'pterodactyl' && $server->external_id) {
            app(PterodactylService::class)->deleteServer((int) $server->external_id);
        } else {
            $proxyId = $server->proxy_id ?? "kafka_{$server->identifier}";
            if ($server->node_id && $server->node) {
                DeleteServerJob::dispatch($server->node, $proxyId);
            }
        }

        $server->delete();
    }

    /**
     * Redeploy a server.
     */
    public function redeploy(Server $server): void
    {
        $server->update(['status' => 'pending']);
        ProvisionServerJob::dispatch($server);
    }

    /**
     * Suspend a server.
     */
    public function suspend(Server $server): void
    {
        Log::info('[Server] Suspending server', ['id' => $server->id]);

        if ($server->provisioning_type === 'pterodactyl' && $server->external_id) {
            app(PterodactylService::class)->setSuspension((int) $server->external_id, true);
        } else {
            $proxyId = $server->proxy_id ?? "kafka_{$server->identifier}";
            if ($server->node_id && $server->node) {
                DeleteServerJob::dispatch($server->node, $proxyId);
            }
        }
        
        $server->update(['status' => 'suspended']);
    }

    /**
     * Suspend all servers for a subscription (trigger proxy deletion but keep DB records).
     */
    public function suspendAll(Subscription $subscription): void
    {
        foreach ($subscription->servers as $server) {
            $this->suspend($server);
        }
    }

    /**
     * Generate a random port within a configurable range that is not used on the node.
     */
    public function generateRandomPort(int $nodeId): int
    {
        $range = Setting::get('port_range', [
            'min' => 20000,
            'max' => 60000,
        ]);

        $usedPorts = Server::where('node_id', $nodeId)
            ->pluck('dest_port')
            ->toArray();

        do {
            $port = rand($range['min'], $range['max']);
        } while (in_array($port, $usedPorts));

        return $port;
    }
}
