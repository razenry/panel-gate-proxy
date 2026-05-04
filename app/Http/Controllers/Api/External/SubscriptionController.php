<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;
use App\Services\ExternalSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function __construct(protected ExternalSyncService $syncService)
    {
    }

    public function sync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'external_id' => 'required|string',
            'email' => 'required|email',
            'plan_id' => 'nullable|string',
            'plan_name' => 'nullable|string',
            'max_server' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:active,suspended,terminated',
            'expired_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $subscription = $this->syncService->syncSubscription($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription synchronized',
                'data' => $subscription
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to sync subscription: ' . $e->getMessage()], 500);
        }
    }

    public function suspend(Request $request)
    {
        $request->validate(['external_id' => 'required|string']);

        try {
            $this->syncService->suspendSubscription($request->input('external_id'));
            return response()->json(['status' => 'success', 'message' => 'Subscription suspended']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function activate(Request $request)
    {
        $request->validate(['external_id' => 'required|string']);

        try {
            $this->syncService->activateSubscription($request->input('external_id'));
            return response()->json(['status' => 'success', 'message' => 'Subscription activated']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function terminate(Request $request)
    {
        $request->validate(['external_id' => 'required|string']);

        try {
            $this->syncService->terminateSubscription($request->input('external_id'));
            return response()->json(['status' => 'success', 'message' => 'Subscription terminated']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
