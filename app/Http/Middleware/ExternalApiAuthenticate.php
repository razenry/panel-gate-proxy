<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        // 1. Check Bearer Token
        $token = $request->bearerToken();
        $expectedToken = config('external_api.key');

        if (!$token || $token !== $expectedToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 2. Check IP Whitelist (REMOVED)

        return $next($request);

    }
}
