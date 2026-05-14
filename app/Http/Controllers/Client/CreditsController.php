<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\TopUpService;
use Illuminate\Http\Request;

class CreditsController extends Controller
{
    public function simulatePayment(string $uuid)
    {
        $invoice = Invoice::where('uuid', $uuid)->where('user_id', auth()->id())->firstOrFail();

        if ($invoice->status !== 'pending') {
            return redirect()->route('client.credits')->with('error', 'Invoice already processed.');
        }

        return view('pages.client.simulate-payment', [
            'invoice' => $invoice,
        ]);
    }

    public function callback(string $uuid, Request $request, TopUpService $topUpService)
    {
        $invoice = Invoice::where('uuid', $uuid)->where('user_id', auth()->id())->firstOrFail();

        if ($request->has('success') && $request->success == 1) {
            try {
                $topUpService->completePayment($invoice, 'sim_'.str()->random(10));

                return redirect()->route('client.credits')->with('success', 'Payment successful! Your credits have been updated.');
            } catch (\Exception $e) {
                return redirect()->route('client.credits')->with('error', 'Error completing payment: '.$e->getMessage());
            }
        } else {
            $topUpService->failPayment($invoice, 'User cancelled or payment failed.');

            return redirect()->route('client.credits')->with('error', 'Payment failed or was cancelled.');
        }
    }
}
