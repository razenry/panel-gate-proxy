<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServerRequest;
use App\Models\Server;
use App\Models\Subscription;
use App\Services\ServerService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ServerService $serverService
    ) {}

    /**
     * Display globally available servers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Server::with(['node', 'subscription']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('subscription_id')) {
            $query->where('subscription_id', $request->subscription_id);
        }

        return $this->success($query->get());
    }

    /**
     * Store a newly created server on behalf of a user.
     */
    public function store(StoreServerRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        $subscription = Subscription::with('user')->findOrFail($data['subscription_id']);

        $server = $this->serverService->create(
            $subscription->user,
            $data
        );

        return $this->success($server, 'Server provisioning started.', 201);
    }

    /**
     * Display the specified server.
     */
    public function show(Server $server): JsonResponse
    {
        return $this->success($server->load(['node', 'subscription']));
    }

    /**
     * Remove the specified server.
     */
    public function destroy(Server $server): JsonResponse
    {
        $this->serverService->delete($server);

        return $this->success(null, 'Server termination started.');
    }

    /**
     * Redeploy a specified server manually.
     */
    public function redeploy(Server $server): JsonResponse
    {
        $this->serverService->redeploy($server);

        return $this->success(null, 'Server redeployment started.');
    }
}
