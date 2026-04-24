<?php

namespace App\Livewire\Admin;

use App\Models\ApiKey;
use App\Services\ApiKeyService;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ApiKeyManager extends Component
{
    public string $description = '';

    public string $expiresAt = '';

    public ?string $newToken = null;

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?string $deleteTargetId = null;

    public string $deleteTargetExpected = '';

    public string $deleteVerificationInput = '';

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['description', 'expiresAt', 'newToken']);
        $this->showModal = true;
    }

    public function createKey(ApiKeyService $service)
    {
        $this->validate([
            'description' => 'required|string|max:255',
            'expiresAt' => 'nullable|date',
        ]);

        $expires = ! empty($this->expiresAt) ? Carbon::parse($this->expiresAt) : null;

        $result = $service->createToken($this->description, $expires);

        $this->newToken = $result['token'];
        $this->reset(['description', 'expiresAt']);

        Flux::toast(text: 'API Key created successfully.', variant: 'success');
    }

    public function closeToken()
    {
        $this->newToken = null;
        $this->showModal = false;
    }

    public function confirmDelete(string $id)
    {
        $key = ApiKey::findOrFail($id);
        $this->deleteTargetId = $key->id;
        $this->deleteTargetExpected = substr($key->id, 0, 8); // e.g. first 8 chars
        $this->deleteVerificationInput = '';
        $this->showDeleteModal = true;
    }

    public function deleteKey()
    {
        if ($this->deleteVerificationInput !== $this->deleteTargetExpected) {
            $this->addError('deleteVerificationInput', 'Verification phrase does not match.');

            return;
        }

        if ($this->deleteTargetId) {
            ApiKey::where('id', $this->deleteTargetId)->delete();
            Flux::toast(text: 'API Key deleted successfully.', variant: 'success');
        }

        $this->showDeleteModal = false;
        $this->reset(['deleteTargetId', 'deleteTargetExpected', 'deleteVerificationInput']);
    }

    public function render()
    {
        $keys = ApiKey::orderBy('created_at', 'desc')->get();

        return view('livewire.admin.api-key-manager', [
            'keys' => $keys,
        ]);
    }
}
