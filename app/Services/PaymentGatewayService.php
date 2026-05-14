<?php

namespace App\Services;

use App\Models\Invoice;

class PaymentGatewayService
{
    /**
     * Get a list of enabled payment gateways.
     */
    public function getEnabledGateways(): array
    {
        // In a real app, this might come from config or database settings
        return [
            [
                'id' => 'stripe',
                'name' => 'Credit Card (Stripe)',
                'icon' => 'credit-card',
                'description' => 'Pay securely with your credit or debit card.',
            ],
            [
                'id' => 'paypal',
                'name' => 'PayPal',
                'icon' => 'currency-dollar',
                'description' => 'Fast and secure payment with your PayPal account.',
            ],
        ];
    }

    /**
     * Initialize a payment session with the chosen gateway.
     */
    public function createPaymentSession(Invoice $invoice): string
    {
        // This is where you would call the actual gateway API (Stripe, PayPal, etc.)
        // For this implementation, we'll return a mock redirect URL
        // or a local route that simulates the payment.

        return match ($invoice->gateway) {
            'stripe' => $this->initStripe($invoice),
            'paypal' => $this->initPayPal($invoice),
            default => throw new \Exception('Unsupported gateway: '.$invoice->gateway),
        };
    }

    protected function initStripe(Invoice $invoice): string
    {
        // Simulate Stripe Checkout Session creation
        return route('client.credits.simulate-payment', ['uuid' => $invoice->uuid, 'gateway' => 'stripe']);
    }

    protected function initPayPal(Invoice $invoice): string
    {
        // Simulate PayPal Order creation
        return route('client.credits.simulate-payment', ['uuid' => $invoice->uuid, 'gateway' => 'paypal']);
    }
}
