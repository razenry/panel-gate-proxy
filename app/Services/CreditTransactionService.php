<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreditTransactionService
{
    /**
     * Create a credit transaction and update user balance.
     *
     * @throws ValidationException
     */
    public function create(
        User $user,
        float $amount,
        string $type,
        string $reason,
        ?int $actorId = null,
        ?string $currencyCode = 'USD',
        ?string $notes = null,
        ?int $referenceId = null,
        ?array $metadata = null
    ): CreditTransaction {
        return DB::transaction(function () use ($user, $amount, $type, $reason, $actorId, $currencyCode, $notes, $referenceId, $metadata) {
            // Lock the user record for update to prevent race conditions
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            $balanceBefore = $user->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Balance safety rule: User balance must never become negative
            if ($balanceAfter < 0) {
                throw ValidationException::withMessages([
                    'amount' => ['Insufficient balance. Resulting balance would be negative ('.$balanceAfter.').'],
                ]);
            }

            // Create transaction record
            $transaction = CreditTransaction::create([
                'user_id' => $user->id,
                'actor_id' => $actorId,
                'type' => $type,
                'amount' => $amount,
                'currency_code' => $currencyCode,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
                'notes' => $notes,
                'reference_id' => $referenceId,
                'metadata' => $metadata,
            ]);

            // Update user balance
            $user->update(['balance' => $balanceAfter]);

            return $transaction;
        });
    }
}
