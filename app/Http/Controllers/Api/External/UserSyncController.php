<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;
use App\Services\ExternalIntegrationService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSyncController extends Controller
{
    use ApiResponses;

    public function __construct(protected ExternalIntegrationService $integrationService)
    {
    }

    /**
     * Create or update user based on email.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string',
        ]);

        $user = $this->integrationService->syncUser($data);
        
        return $this->success([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ], 'User synchronized successfully.');
    }
}
