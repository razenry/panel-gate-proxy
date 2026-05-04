<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Log a user activity.
     */
    protected function logActivity(string $description): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
