<?php

namespace App\Services;

use App\Actions\ReverseCreditAction;
use App\Models\CreditTransaction;
use App\Models\User;

class CreditService
{
    public function __construct(
        protected CreditTransactionService $transactionService
    ) {}

    /**
     * Add credit to a user.
     */
    public function addCredit(User $user, float $amount, string $reason, ?int $actorId = null, ?string $currencyCode = 'USD', ?string $notes = null, ?array $metadata = null): CreditTransaction
    {
        return $this->transactionService->create(
            user: $user,
            amount: abs($amount),
            type: 'credit',
            reason: $reason,
            actorId: $actorId,
            currencyCode: $currencyCode,
            notes: $notes,
            metadata: $metadata
        );
    }

    /**
     * Remove credit (debit) from a user.
     */
    public function removeCredit(User $user, float $amount, string $reason, ?int $actorId = null, ?string $currencyCode = 'USD', ?string $notes = null, ?array $metadata = null): CreditTransaction
    {
        return $this->transactionService->create(
            user: $user,
            amount: -abs($amount),
            type: 'debit',
            reason: $reason,
            actorId: $actorId,
            currencyCode: $currencyCode,
            notes: $notes,
            metadata: $metadata
        );
    }

    /**
     * Reverse a transaction.
     */
    public function reverseTransaction(CreditTransaction $transaction, ?int $actorId = null, ?string $reason = null): CreditTransaction
    {
        return app(ReverseCreditAction::class)->execute($transaction, $actorId, $reason);
    }
}
