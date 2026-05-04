<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * List user activity logs.
     */
    public function index()
    {
        $logs = ActivityLog::where('user_id', Auth::id())
            ->latest()
            ->paginate(50);

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }
}
