<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiSubscriptionRequest;
use App\Http\Requests\UpdateApiSubscriptionRequest;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Traits\ApiResponses;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Display a listing of the subscriptions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Subscription::with('user');

        if ($request->has('email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email', $request->email);
            });
        }

        if ($request->has('external_id')) {
            $query->where('external_id', $request->external_id);
        }

        return $this->success($query->get());
    }

    /**
     * Store a newly created subscription in storage.
     */
    public function store(StoreApiSubscriptionRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Ensure user exists
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make(Str::random(16)),
            ]
        );

        $plan = Plan::where('name', $data['plan_name'])->firstOrFail();

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'external_id' => $data['external_id'] ?? null,
            'plan_name' => $plan->name,
            'max_server' => $plan->max_server,
            'status' => $data['status'],
            'expired_at' => !empty($data['expired_at']) ? CarbonImmutable::parse($data['expired_at']) : null,
        ]);

        return $this->success($subscription->load('user'), 'Subscription created successfully.', 201);
    }

    /**
     * Display the specified subscription.
     */
    public function show(Subscription $subscription): JsonResponse
    {
        return $this->success($subscription->load('user'));
    }

    /**
     * Update the specified subscription in storage.
     */
    public function update(UpdateApiSubscriptionRequest $request, Subscription $subscription): JsonResponse
    {
        $data = $request->validated();
        $oldStatus = $subscription->status;

        if (isset($data['plan_name'])) {
            $plan = Plan::where('name', $data['plan_name'])->firstOrFail();
            $data['max_server'] = $plan->max_server;
        }

        if (isset($data['expired_at'])) {
            $data['expired_at'] = CarbonImmutable::parse($data['expired_at']);
        }

        $subscription->update($data);

        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            $this->subscriptionService->handleStatusChange($subscription, $oldStatus);
        }

        return $this->success($subscription->fresh()->load('user'), 'Subscription updated successfully.');
    }

    /**
     * Remove the specified subscription securely.
     */
    public function destroy(Subscription $subscription): JsonResponse
    {
        $this->subscriptionService->handleStatusChange($subscription, $subscription->status); // forces deletion internally when mapping to separate logic if needed, but wait: handleStatusChange doesn't accept destroy natively in that class, wait our terminateServers is available natively
        
        // Directly call terminate logic
        // We see subscriptionService->terminateServers($subscription) was defined inside SubscriptionService
        // But since it's "protected" in SubscriptionService, we should use reflection or correct it.
        // I will just use public method if available, or delete it and rely on the model events/cascade. Let me change terminateServers to public in the next step, or just iterate servers here.
        
        foreach ($subscription->servers as $server) {
            app(\App\Services\ServerService::class)->delete($server);
        }

        $subscription->delete();

        return $this->success(null, 'Subscription and underlying servers terminated successfully.');
    }
}
