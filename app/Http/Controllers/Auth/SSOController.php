<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ExternalSyncService;
use App\Services\SSOService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SSOController extends Controller
{
    public function __construct(
        protected SSOService $ssoService,
        protected ExternalSyncService $syncService
    ) {
    }

    /**
     * Handle SSO login via JWT token.
     */
    public function login(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'SSO token missing.');
        }

        $payload = $this->ssoService->validateToken($token);

        if (!$payload || empty($payload['email'])) {
            return view('auth.sso-error', ['message' => 'Invalid or expired SSO token.']);
        }

        return view('auth.sso-loading', [
            'token' => $token,
            'email' => $payload['email']
        ]);
    }

    /**
     * Finalize the SSO login after the loading screen.
     */
    public function finalize(Request $request)
    {
        $token = $request->input('token');
        $payload = $this->ssoService->validateToken($token);

        if (!$payload || empty($payload['email'])) {
            return redirect()->route('login')->with('error', 'Authentication failed.');
        }

        try {
            // Find or create user
            $user = $this->syncService->syncUser(
                $payload['email'],
                $payload['name'] ?? explode('@', $payload['email'])[0]
            );

            // Log user in
            Auth::login($user);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            logger()->error('SSO Login failed: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'SSO login failed. Please contact support.');
        }
    }
}
