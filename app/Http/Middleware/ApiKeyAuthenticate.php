<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthenticated. API key missing.'], 401);
        }

        try {
            $secret = config('app.key');
            if (Str::startsWith($secret, 'base64:')) {
                $secret = base64_decode(substr($secret, 7));
            }

            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthenticated. Invalid or expired token.'], 401);
        }

        $apiKey = ApiKey::find($decoded->jti);

        if (! $apiKey) {
            return response()->json(['message' => 'Unauthenticated. API key not found or revoked.'], 401);
        }

        if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
            return response()->json(['message' => 'Unauthenticated. API key has expired.'], 401);
        }

        if ($apiKey->token !== hash('sha256', $token)) {
            return response()->json(['message' => 'Unauthenticated. Token mismatch.'], 401);
        }

        $request->attributes->set('api_key', $apiKey);

        if ($apiKey->user_id) {
            Auth::loginUsingId($apiKey->user_id);
        }

        return $next($request);
    }
}
