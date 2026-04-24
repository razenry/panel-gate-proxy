<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\ApiKeyAuthenticate;
use App\Models\User;
use App\Services\ApiKeyService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class ApiKeyAuthenticateTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_rejects_missing_token(): void
    {
        $middleware = new ApiKeyAuthenticate;
        $request = Request::create('/api/test', 'GET');

        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertStringContainsString('API key missing', $response->getContent());
    }

    public function test_middleware_accepts_valid_token(): void
    {
        $service = new ApiKeyService;
        $result = $service->createToken('Test Key');

        $middleware = new ApiKeyAuthenticate;
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('Authorization', 'Bearer '.$result['token']);

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_rejects_expired_token(): void
    {
        $service = new ApiKeyService;
        $result = $service->createToken('Expired Key', Carbon::now()->subDay());

        $middleware = new ApiKeyAuthenticate;
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('Authorization', 'Bearer '.$result['token']);

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_middleware_logs_in_user_if_assigned(): void
    {
        $user = User::factory()->create();
        $service = new ApiKeyService;
        $result = $service->createToken('User Key', null, $user->id);

        $middleware = new ApiKeyAuthenticate;
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('Authorization', 'Bearer '.$result['token']);

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($user->id, auth()->id());
    }
}
