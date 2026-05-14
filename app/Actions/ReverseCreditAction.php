<?php

namespace App\Actions;

use App\Models\CreditTransaction;
use App\Services\CreditTransactionService;
use Illuminate\Validation\ValidationException;

class ReverseCreditAction
{
    public function __construct(
        protected CreditTransactionService $transactionService
    ) {}

    /**
     * Execute the reversal of a credit transaction.
     */
    public function execute(CreditTransaction $transaction, ?int $actorId = null, ?string $reason = null): CreditTransaction
    {
        // Prevent duplicate reversal
        if (CreditTransaction::where('reference_id', $transaction->id)->where('type', 'reversal')->exists()) {
            throw ValidationException::withMessages([
                'transaction' => ['This transaction has already been reversed.'],
            ]);
        }

        // A reversal of a reversal is generally not allowed or should be handled carefully.
        // For this system, we'll just allow reversing credits and debits.
        if ($transaction->type === 'reversal') {
            throw ValidationException::withMessages([
                'transaction' => ['Cannot reverse a reversal transaction.'],
            ]);
        }

        $reversalAmount = -$transaction->amount;
        $reversalReason = $reason ?? "Reversal of transaction #{$transaction->id}: {$transaction->reason}";

        return $this->transactionService->create(
            user: $transaction->user,
            amount: $reversalAmount,
            type: 'reversal',
            reason: $reversalReason,
            actorId: $actorId,
            currencyCode: $transaction->currency_code,
            referenceId: $transaction->id,
            metadata: [
                'original_transaction_id' => $transaction->id,
                'reversal_timestamp' => now()->toIso8601String(),
            ]
        );
    }
}
