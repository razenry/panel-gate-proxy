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

    /**
     * Get the nodes for the location.
     */
    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class);
    }
}
