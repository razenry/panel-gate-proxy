<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'short',
        'long',
    ];

    protected $appends = ['label'];

    /**
     * Alias long as label for compatibility with external systems.
     */
    public function getLabelAttribute(): string
    {
        return $this->long;
    }

    /**
     * Get the nodes for the location.
     */
    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class);
    }
}
