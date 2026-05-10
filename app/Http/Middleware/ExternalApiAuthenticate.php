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

class ExternalApiAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized. API Key missing.'], 401);
        }

        try {
            // 1. Decode JWT to get the key ID (jti)
            $secret = config('app.key');
            if (Str::startsWith($secret, 'base64:')) {
                $secret = base64_decode(substr($secret, 7));
            }

            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            
            // 2. Find Key in Database
            $apiKey = ApiKey::find($decoded->jti);

            if (!$apiKey) {
                return response()->json(['message' => 'Unauthorized. Invalid API Key.'], 401);
            }

            // 3. Verify Token Hash
            if ($apiKey->token !== hash('sha256', $token)) {
                return response()->json(['message' => 'Unauthorized. Token mismatch.'], 401);
            }

            // 4. Check Expiration
            if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
                return response()->json(['message' => 'Unauthorized. API Key has expired.'], 401);
            }

            // 5. Update Monitoring/Usage Stats
            $apiKey->update([
                'last_used_at' => now(),
                'last_used_ip' => $request->ip(),
                'request_count' => $apiKey->request_count + 1,
            ]);

            // 6. Detailed Usage Logging
            $apiKey->usageLogs()->create([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'endpoint' => $request->fullUrl(),
                'method' => $request->method(),
            ]);

            // 7. Auto-login if key belongs to a user (optional but helpful for sync)
            if ($apiKey->user_id) {
                Auth::loginUsingId($apiKey->user_id);
            }

            return $next($request);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized. ' . $e->getMessage()], 401);
        }
    }
}
