<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SSHKey extends Model
{
    use HasFactory;

    protected $table = 'ssh_keys';

    protected $fillable = [
        'user_id',
        'name',
        'public_key',
        'fingerprint',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
