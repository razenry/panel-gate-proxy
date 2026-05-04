<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;
use App\Services\ExternalSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct(protected ExternalSyncService $syncService)
    {
    }

    public function sync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = $this->syncService->syncUser(
                $request->input('email'),
                $request->input('name')
            );

            return response()->json([
                'status' => 'success',
                'message' => 'User synchronized',
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to sync user: ' . $e->getMessage()], 500);
        }
    }
}
