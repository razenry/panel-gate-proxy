<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\SSHKey;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SSHKeyController extends Controller
{
    use LogsActivity;

    /**
     * List all SSH keys for the authenticated user.
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => SSHKey::where('user_id', Auth::id())->get(),
        ]);
    }

    /**
     * Store a new SSH key.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'public_key' => ['required', 'string'],
        ]);

        $publicKey = trim($request->public_key);
        $fingerprint = $this->generateFingerprint($publicKey);

        if (! $fingerprint) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid SSH public key format.',
            ], 422);
        }

        if (SSHKey::where('user_id', Auth::id())->where('fingerprint', $fingerprint)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This SSH key is already registered to your account.',
            ], 422);
        }

        $sshKey = SSHKey::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'public_key' => $publicKey,
            'fingerprint' => $fingerprint,
        ]);

        $this->logActivity("Added SSH key: {$request->name}");

        return response()->json([
            'status' => 'success',
            'message' => 'SSH key added successfully.',
            'data' => $sshKey,
        ]);
    }

    /**
     * Remove an SSH key by fingerprint.
     */
    public function remove(Request $request)
    {
        $request->validate([
            'fingerprint' => ['required', 'string'],
        ]);

        $sshKey = SSHKey::where('user_id', Auth::id())
            ->where('fingerprint', $request->fingerprint)
            ->firstOrFail();

        $name = $sshKey->name;
        $sshKey->delete();

        $this->logActivity("Removed SSH key: {$name}");

        return response()->json([
            'status' => 'success',
            'message' => 'SSH key removed successfully.',
        ]);
    }

    /**
     * Generate SHA256 fingerprint for SSH public key.
     */
    protected function generateFingerprint(string $publicKey): ?string
    {
        $parts = explode(' ', $publicKey);
        if (count($parts) < 2) {
            return null;
        }

        $data = base64_decode($parts[1], true);
        if (! $data) {
            return null;
        }

        return 'SHA256:' . base64_encode(hash('sha256', $data, true));
    }
}
