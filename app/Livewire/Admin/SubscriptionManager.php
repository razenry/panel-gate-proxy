<?php

namespace App\Livewire\Admin;

use App\Models\Plan;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonImmutable;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SubscriptionManager extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingSubId = null;

    // Create/Edit fields
    public $manageSubUserId = '';
    public $selectedPlanId = '';
    public $expiredAt = '';
    public $status = 'active';
    
    // User Search for Create
    public string $userSearch = '';
    public $foundUsers = [];

    // Secure Delete State
    public bool $showDeleteModal = false;
    public ?int $deleteTargetId = null;
    public string $deleteTargetExpected = '';
    public string $deleteVerificationInput = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedUserSearch()
    {
        if (strlen($this->userSearch) > 1) {
            $this->foundUsers = User::where('name', 'like', '%' . $this->userSearch . '%')
                ->orWhere('email', 'like', '%' . $this->userSearch . '%')
                ->limit(20)
                ->get()
                ->toArray();
        } else {
            $this->foundUsers = [];
        }
    }

    public function selectUser($id, $name)
    {
        $this->manageSubUserId = $id;
        $this->userSearch = $name;
        $this->foundUsers = [];
    }

    public function deselectUser()
    {
        $this->manageSubUserId = null;
        $this->userSearch = '';
    }

    public function openModal(?int $subId = null)
    {
        $this->resetValidation();
        $this->reset(['manageSubUserId', 'selectedPlanId', 'expiredAt', 'status', 'userSearch', 'foundUsers']);
        $this->editingSubId = $subId;

        if ($subId) {
            $sub = Subscription::with('user')->findOrFail($subId);
            $this->manageSubUserId = $sub->user_id;
            $this->userSearch = $sub->user?->name ?? 'Unknown user';
            
            // To find the matched plan
            $plan = Plan::where('name', $sub->plan_name)->first();
            $this->selectedPlanId = $plan?->id;
            
            $this->status = $sub->status;
            $this->expiredAt = $sub->expired_at ? $sub->expired_at->format('Y-m-d\TH:i') : null;
        }

        $this->showModal = true;
    }

    protected function calculateExpiry(): CarbonImmutable
    {
        $duration = Setting::get('subscription_duration_default', ['value' => 30, 'unit' => 'days']);
        if (!is_array($duration)) {
            $duration = ['value' => 30, 'unit' => 'days'];
        }
        $val = max(1, (int) ($duration['value'] ?? 30));
        $unit = $duration['unit'] ?? 'days';

        return match ($unit) {
            'months' => now()->addMonths($val),
            'years' => now()->addYears($val),
            default => now()->addDays($val),
        };
    }

    public function save()
    {
        $this->validate([
            'manageSubUserId' => 'required|exists:users,id',
            'selectedPlanId' => 'required|exists:plans,id',
            'status' => 'required|in:active,suspended,expired',
            'expiredAt' => 'nullable|date',
        ]);

        $plan = Plan::findOrFail($this->selectedPlanId);
        $user = User::findOrFail($this->manageSubUserId);
        $expiry = $this->expiredAt ? CarbonImmutable::parse($this->expiredAt) : $this->calculateExpiry();

        if ($this->editingSubId) {
            Subscription::findOrFail($this->editingSubId)->update([
                'user_id' => $user->id,
                'plan_name' => $plan->name,
                'max_server' => $plan->max_server,
                'status' => $this->status,
                'expired_at' => $expiry,
            ]);
            Flux::toast(text: 'Subscription updated successfully.', variant: 'success');
        } else {
            Subscription::create([
                'user_id' => $user->id,
                'external_id' => 'manual_' . Str::random(8),
                'plan_name' => $plan->name,
                'max_server' => $plan->max_server,
                'status' => $this->status,
                'expired_at' => $expiry,
            ]);
            Flux::toast(text: 'Subscription created successfully.', variant: 'success');
        }

        $this->showModal = false;
    }

    public function confirmDelete(int $subId)
    {
        $sub = Subscription::findOrFail($subId);
        $this->deleteTargetId = $sub->id;
        $this->deleteTargetExpected = 'REVOKE';
        $this->deleteVerificationInput = '';
        $this->showDeleteModal = true;
    }

    public function executeDelete()
    {
        if ($this->deleteVerificationInput !== $this->deleteTargetExpected) {
            $this->addError('deleteVerificationInput', 'Verification phrase does not match.');
            return;
        }

        $sub = Subscription::findOrFail($this->deleteTargetId);
        if ($sub->servers()->exists()) {
            $this->addError('deleteVerificationInput', 'Cannot revoke. Terminate user proxy servers on this subscription first.');
            return;
        }

        $sub->delete();
        Flux::toast(text: 'Subscription revoked successfully.', variant: 'success');
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $subscriptions = Subscription::with('user')->withCount('servers')
            ->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orWhere('external_id', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $plans = Plan::all();

        return view('livewire.admin.subscription-manager', [
            'subscriptions' => $subscriptions,
            'plans' => $plans,
        ]);
    }
}
