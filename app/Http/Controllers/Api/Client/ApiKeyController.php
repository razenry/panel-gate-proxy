<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Traits\LogsActivity;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    use LogsActivity;

    /**
     * List all API keys for the authenticated user.
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => ApiKey::where('user_id', Auth::id())->get(),
        ]);
    }

    /**
     * Create a new API key.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'allowed_ips' => ['nullable', 'string'],
        ]);

        $apiKey = ApiKey::create([
            'user_id' => Auth::id(),
            'description' => $request->description,
            'allowed_ips' => $request->allowed_ips,
        ]);

        $secret = config('app.key');
        if (Str::startsWith($secret, 'base64:')) {
            $secret = base64_decode(substr($secret, 7));
        }

        $payload = [
            'iat' => time(),
            'jti' => $apiKey->id,
            'sub' => Auth::id(),
        ];

        $token = JWT::encode($payload, $secret, 'HS256');
        
        // We store the hash of the token for verification, as per standard practice (though the existing middleware checks it)
        $apiKey->update(['token' => hash('sha256', $token)]);

        $this->logActivity("Created new API key: {$request->description}");

        return response()->json([
            'status' => 'success',
            'message' => 'API key created successfully.',
            'data' => [
                'identifier' => $apiKey->id,
                'description' => $apiKey->description,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Delete an API key.
     */
    public function destroy($id)
    {
        $apiKey = ApiKey::where('user_id', Auth::id())->findOrFail($id);
        $description = $apiKey->description;
        $apiKey->delete();

        $this->logActivity("Deleted API key: {$description}");

        return response()->json([
            'status' => 'success',
            'message' => 'API key deleted successfully.',
        ]);
    }
}
