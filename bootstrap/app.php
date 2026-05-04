<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnforceTwoFactor;
use App\Http\Middleware\Impersonate;
use App\Http\Middleware\IpWhitelist;
use App\Http\Middleware\ApiKeyAuthenticate;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('subscription:check')->everyFiveMinutes();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            '2fa.enforce' => EnforceTwoFactor::class,
            'ip_whitelist' => IpWhitelist::class,
            'api_key' => ApiKeyAuthenticate::class,
            'external_api' => \App\Http\Middleware\ExternalApiAuthenticate::class,
        ]);


        $middleware->appendToGroup('web', [
            Impersonate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage() ?: \Symfony\Component\HttpFoundation\Response::$statusTexts[$e->getStatusCode()] ?? 'Error',
                ], $e->getStatusCode());
            }
        });

        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Internal Server Error.',
                    // 'debug' => $e->getMessage(), // Only enable on strict debug environments
                ], 500);
            }
        });
    })->create();
