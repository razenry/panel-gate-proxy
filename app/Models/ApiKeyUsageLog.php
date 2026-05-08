<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKeyUsageLog extends Model
{
    protected $fillable = [
        'api_key_id',
        'ip_address',
        'user_agent',
        'endpoint',
        'method',
    ];

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
