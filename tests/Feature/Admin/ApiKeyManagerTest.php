<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\ApiKeyManager;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ApiKeyManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_for_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(ApiKeyManager::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.admin.api-key-manager');
    }

    public function test_admin_can_create_api_key(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(ApiKeyManager::class)
            ->set('description', 'Test Key')
            ->call('createKey')
            ->assertHasNoErrors()
            ->assertSet('description', '')
            ->assertSet('newToken', function ($value) {
                return ! empty($value);
            });

        $this->assertDatabaseHas('api_keys', [
            'description' => 'Test Key',
        ]);
    }

    public function test_admin_can_delete_api_key(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $keyId = (string) Str::uuid();
        $key = new ApiKey;
        $key->id = $keyId;
        $key->token = 'hashed_token';
        $key->description = 'Old Key';
        $key->save();

        Livewire::actingAs($admin)
            ->test(ApiKeyManager::class)
            ->call('confirmDelete', $keyId)
            ->set('deleteVerificationInput', substr($keyId, 0, 8))
            ->call('deleteKey');

        $this->assertDatabaseMissing('api_keys', [
            'id' => $keyId,
        ]);
    }
}
