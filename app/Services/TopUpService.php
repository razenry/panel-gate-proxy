<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TopUpService
{
    public function __construct(
        protected CreditTransactionService $transactionService
    ) {}

    /**
     * Create a top-up invoice.
     */
    public function createInvoice(User $user, float $amount, string $gateway, string $currency = 'USD'): Invoice
    {
        // Validation for min/max
        $min = 5.00;
        $max = 1000.00;

        if ($amount < $min) {
            throw ValidationException::withMessages(['amount' => ["Minimum top-up amount is {$min} {$currency}."]]);
        }

        if ($amount > $max) {
            throw ValidationException::withMessages(['amount' => ["Maximum top-up amount is {$max} {$currency}."]]);
        }

        return Invoice::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'gateway' => $gateway,
            'currency' => $currency,
            'status' => 'pending',
        ]);
    }

    /**
     * Complete a top-up payment.
     */
    public function completePayment(Invoice $invoice, string $gatewayReference): CreditTransaction
    {
        if ($invoice->status !== 'pending') {
            throw new \Exception('This invoice has already been processed.');
        }

        return DB::transaction(function () use ($invoice, $gatewayReference) {
            // Update invoice status
            $invoice->update([
                'status' => 'paid',
                'gateway_id' => $gatewayReference,
            ]);

            // Create credit transaction
            return $this->transactionService->create(
                user: $invoice->user,
                amount: $invoice->amount,
                type: 'topup',
                reason: "Top-up via {$invoice->gateway} (Ref: {$gatewayReference})",
                actorId: null, // System action
                currencyCode: $invoice->currency,
                notes: "Invoice ID: {$invoice->id}",
                metadata: [
                    'invoice_id' => $invoice->id,
                    'gateway' => $invoice->gateway,
                    'gateway_reference' => $gatewayReference,
                ]
            );
        });
    }

    /**
     * Fail a top-up payment.
     */
    public function failPayment(Invoice $invoice, ?string $reason = null): void
    {
        $invoice->update([
            'status' => 'failed',
            'metadata' => array_merge($invoice->metadata ?? [], ['fail_reason' => $reason]),
        ]);
    }
}
