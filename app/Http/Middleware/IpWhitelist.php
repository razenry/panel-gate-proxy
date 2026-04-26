<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $whitelistStr = config('api.whitelist', env('API_WHITELIST_IPS', ''));
        
        if (empty(trim($whitelistStr))) {
            return $next($request);
        }

        $allowedIps = array_map('trim', explode(',', $whitelistStr));

        if (!in_array($request->ip(), $allowedIps)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized IP address.',
            ], 403);
        }

        return $next($request);
    }
}
