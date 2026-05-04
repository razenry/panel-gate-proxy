<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    use LogsActivity;

    /**
     * Get authenticated user data.
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Auth::user(),
        ]);
    }

    /**
     * Update user email.
     */
    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email,' . Auth::id()],
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        $oldEmail = $user->email;
        $user->email = $request->email;
        $user->save();

        $this->logActivity("Updated email from {$oldEmail} to {$request->email}");

        return response()->json([
            'status' => 'success',
            'message' => 'Email updated successfully.',
        ]);
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        $this->logActivity("Updated account password");

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully.',
        ]);
    }
}
