<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'amount',
        'currency_code',
        'balance_before',
        'balance_after',
        'reason',
        'notes',
        'reference_id',
        'metadata',
        'status',
        'payment_gateway',
        'payment_reference',
        'invoice_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'json',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the actor (admin/system) that performed the transaction.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get the reference transaction (for reversals).
     */
    public function reference(): BelongsTo
    {
        return $this->belongsTo(CreditTransaction::class, 'reference_id');
    }

    /**
     * Scope for credit types.
     */
    public function scopeCredit($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope for debit types.
     */
    public function scopeDebit($query)
    {
        return $query->where('type', 'debit');
    }

    /**
     * Scope for reversal types.
     */
    public function scopeReversal($query)
    {
        return $query->where('type', 'reversal');
    }
}
