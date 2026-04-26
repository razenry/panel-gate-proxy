<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServerRequest;
use App\Models\Server;
use App\Services\ServerService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

class ServerController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ServerService $serverService
    ) {}

    /**
     * Display a listing of the user's servers.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error('Unauthenticated.', 401);
        }

        $servers = $user->servers()->with(['node', 'subscription'])->get();

        return $this->success($servers);
    }

    /**
     * Provision a new reverse proxy server.
     */
    public function store(StoreServerRequest $request): JsonResponse
    {
        $this->authorize('create', Server::class);

        $server = $this->serverService->create(
            $request->user(),
            $request->validated()
        );

        return $this->success($server, 'Server provisioning started.', 201);
    }

    /**
     * Display the specified server.
     */
    public function show(Server $server): JsonResponse
    {
        $this->authorize('view', $server);

        return $this->success($server->load(['node', 'subscription']));
    }

    /**
     * Delete the specified server.
     */
    public function destroy(Server $server): JsonResponse
    {
        $this->authorize('delete', $server);

        $this->serverService->delete($server);

        return $this->success(null, 'Server termination started.');
    }
}
