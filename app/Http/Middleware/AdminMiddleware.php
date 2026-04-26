<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->attributes->has('api_key')) {
            return $next($request);
        }

        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, 'Unauthorized action. You must be an admin.');
        }

        return $next($request);
    }
}
