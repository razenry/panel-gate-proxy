<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PterodactylService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(Setting::get('pterodactyl_url', ''), '/');
        $this->apiKey = Setting::get('pterodactyl_api_key', '');
    }

    /**
     * Create a server on Pterodactyl.
     */
    public function createServer(array $data): array
    {
        Log::info('[Pterodactyl] Attempting to create server', $data);

        if (empty($this->baseUrl) || empty($this->apiKey)) {
            Log::error('[Pterodactyl] Base URL or API Key not configured.');
            throw new \RuntimeException('Pterodactyl is not configured.');
        }

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->post("{$this->baseUrl}/api/application/servers", [
                'name' => $data['label'],
                'user' => $data['pterodactyl_user_id'] ?? Setting::get('pterodactyl_default_user_id'),
                'nest' => $data['nest_id'] ?? Setting::get('pterodactyl_default_nest_id'),
                'egg' => $data['egg_id'] ?? Setting::get('pterodactyl_default_egg_id'),
                'docker_image' => $data['docker_image'] ?? Setting::get('pterodactyl_default_docker_image'),
                'startup' => $data['startup'] ?? Setting::get('pterodactyl_default_startup'),
                'limits' => [
                    'memory' => $data['memory'] ?? 1024,
                    'swap' => 0,
                    'disk' => $data['disk'] ?? 5120,
                    'io' => 500,
                    'cpu' => $data['cpu'] ?? 100,
                ],
                'feature_limits' => [
                    'databases' => 0,
                    'allocations' => 0,
                    'backups' => 0,
                ],
                'allocation' => [
                    'default' => $data['allocation_id'] ?? Setting::get('pterodactyl_default_allocation_id'),
                ],
                'environment' => $data['environment'] ?? [],
                'start_on_completion' => true,
            ]);

        if ($response->failed()) {
            Log::error('[Pterodactyl] Failed to create server', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \RuntimeException('Failed to create server on Pterodactyl: ' . ($response->json()['errors'][0]['detail'] ?? 'Unknown error'));
        }

        $serverData = $response->json()['attributes'];
        Log::info('[Pterodactyl] Server created successfully', ['id' => $serverData['id']]);

        return $serverData;
    }

    /**
     * Delete a server from Pterodactyl.
     */
    public function deleteServer(int $externalId): bool
    {
        Log::info('[Pterodactyl] Attempting to delete server', ['external_id' => $externalId]);

        $response = Http::withToken($this->apiKey)
            ->delete("{$this->baseUrl}/api/application/servers/{$externalId}");

        if ($response->failed() && $response->status() !== 404) {
            Log::error('[Pterodactyl] Failed to delete server', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            return false;
        }

        Log::info('[Pterodactyl] Server deleted successfully or already gone');
        return true;
    }

    /**
     * Suspend/Unsuspend server.
     */
    public function setSuspension(int $externalId, bool $suspended): bool
    {
        $action = $suspended ? 'suspend' : 'unsuspend';
        Log::info("[Pterodactyl] Attempting to {$action} server", ['external_id' => $externalId]);

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/api/application/servers/{$externalId}/{$action}");

        if ($response->failed()) {
            Log::error("[Pterodactyl] Failed to {$action} server", [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            return false;
        }

        return true;
    }
}
