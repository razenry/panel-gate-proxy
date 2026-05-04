<?php

namespace Tests\Feature\Api\Client;

use App\Models\ApiKey;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected function generateToken(User $user, array $options = []): string
    {
        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'description' => 'Test Key',
            'allowed_ips' => $options['allowed_ips'] ?? null,
        ]);

        $secret = config('app.key');
        if (Str::startsWith($secret, 'base64:')) {
            $secret = base64_decode(substr($secret, 7));
        }

        $payload = [
            'iat' => time(),
            'jti' => $apiKey->id,
            'sub' => $user->id,
        ];

        $token = JWT::encode($payload, $secret, 'HS256');
        $apiKey->update(['token' => hash('sha256', $token)]);

        return $token;
    }

    public function test_can_get_account_info()
    {
        $user = User::factory()->create();
        $token = $this->generateToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/client/account');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_cannot_access_without_token()
    {
        $response = $this->getJson('/api/client/account');

        $response->assertStatus(401);
    }

    public function test_can_create_api_key()
    {
        $user = User::factory()->create();
        $token = $this->generateToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/client/account/api-keys', [
                'description' => 'New API Key',
                'allowed_ips' => '127.0.0.1',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['identifier', 'description', 'token'],
            ]);

        $this->assertDatabaseHas('api_keys', [
            'user_id' => $user->id,
            'description' => 'New API Key',
            'allowed_ips' => '127.0.0.1',
        ]);
    }

    public function test_api_key_respects_ip_whitelist()
    {
        $user = User::factory()->create();
        $token = $this->generateToken($user, ['allowed_ips' => '1.1.1.1']);

        // Requesting from localhost (usually 127.0.0.1 in tests)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/client/account');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized IP address.']);
    }

    public function test_can_update_email_with_password_confirmation()
    {
        $user = User::factory()->create([
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);
        $token = $this->generateToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/client/account/email', [
                'email' => 'newemail@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('newemail@example.com', $user->fresh()->email);
    }
}
