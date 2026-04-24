<?php

namespace App\Services;

use App\Models\ApiKey;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;

class ApiKeyService
{
    /**
     * Create a new API Key securely signed with JWT.
     * Returns an array with the raw JWT token and the created model.
     *
     * @return array{token: string, key: ApiKey}
     */
    public function createToken(string $description, ?Carbon $expiresAt = null, ?int $userId = null): array
    {
        $id = (string) Str::uuid();

        $payload = [
            'iss' => config('app.url'),
            'jti' => $id,
            'iat' => now()->timestamp,
        ];

        if ($expiresAt) {
            $payload['exp'] = $expiresAt->timestamp;
        }

        $secret = config('app.key');
        // Handle config('app.key') starting with 'base64:'
        if (Str::startsWith($secret, 'base64:')) {
            $secret = base64_decode(substr($secret, 7));
        }

        $jwt = JWT::encode($payload, $secret, 'HS256');

        $key = new ApiKey;
        $key->id = $id;
        $key->token = hash('sha256', $jwt);
        $key->user_id = $userId;
        $key->description = $description;
        $key->expires_at = $expiresAt;
        $key->save();

        return [
            'token' => $jwt,
            'key' => $key,
        ];
    }
}
