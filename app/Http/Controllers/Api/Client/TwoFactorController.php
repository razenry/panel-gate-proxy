<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;

class TwoFactorController extends Controller
{
    use LogsActivity;

    /**
     * Get 2FA QR setup data.
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user->two_factor_secret) {
            return response()->json([
                'status' => 'error',
                'message' => 'Two factor authentication is not initialized.',
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'qr_code' => $user->twoFactorQrCodeSvg(),
                'setup_key' => decrypt($user->two_factor_secret),
                'confirmed' => ! is_null($user->two_factor_confirmed_at),
            ],
        ]);
    }

    /**
     * Enable/Confirm 2FA.
     */
    public function store(Request $request, EnableTwoFactorAuthentication $enable, ConfirmTwoFactorAuthentication $confirm)
    {
        $user = Auth::user();

        if (! $user->two_factor_secret) {
            $enable($user);
            $this->logActivity("Initialized 2FA setup");
            
            return response()->json([
                'status' => 'success',
                'message' => 'Two factor authentication initialized. Please confirm with a code.',
                'data' => [
                    'qr_code' => $user->twoFactorQrCodeSvg(),
                    'setup_key' => decrypt($user->two_factor_secret),
                ]
            ]);
        }

        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $confirm($user, $request->code);
        $this->logActivity("Enabled 2FA");

        return response()->json([
            'status' => 'success',
            'message' => 'Two factor authentication enabled successfully.',
        ]);
    }

    /**
     * Disable 2FA.
     */
    public function destroy(DisableTwoFactorAuthentication $disable)
    {
        $disable(Auth::user());
        $this->logActivity("Disabled 2FA");

        return response()->json([
            'status' => 'success',
            'message' => 'Two factor authentication disabled successfully.',
        ]);
    }
}
