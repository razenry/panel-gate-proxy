<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;
use App\Services\ExternalIntegrationService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionSyncController extends Controller
{
    use ApiResponses;

    public function __construct(protected ExternalIntegrationService $integrationService)
    {
    }

    /**
     * Create or update subscription.
     */
    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'external_id' => 'required|string',
            'email' => 'required|email',
            'name' => 'nullable|string',
            'plan_name' => 'required|string',
            'max_server' => 'nullable|integer',
            'expired_at' => 'nullable|date',
            'status' => 'nullable|string',
        ]);

        $subscription = $this->integrationService->syncSubscription($data);
        
        return $this->success($subscription, 'Subscription synchronized successfully.');
    }

    /**
     * Activate subscription.
     */
    public function activate(Request $request, string $externalId): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string',
            'plan_name' => 'required|string',
            'max_server' => 'nullable|integer',
            'expired_at' => 'nullable|date',
        ]);

        $subscription = $this->integrationService->activateSubscription($externalId, $data);
        
        return $this->success($subscription, 'Subscription activated successfully.');
    }

    /**
     * Suspend subscription.
     */
    public function suspend(Request $request, string $externalId): JsonResponse
    {
        $subscription = $this->integrationService->suspendSubscription($externalId);
        
        return $this->success($subscription, 'Subscription suspended successfully.');
    }

    /**
     * Terminate subscription.
     */
    public function terminate(Request $request, string $externalId): JsonResponse
    {
        $subscription = $this->integrationService->terminateSubscription($externalId);
        
        return $this->success($subscription, 'Subscription terminated successfully.');
    }
}
