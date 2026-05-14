<x-layouts::app :title="__('Process Payment')">
    <div class="max-w-xl mx-auto py-12">
        <flux:card class="p-8 text-center space-y-6">
            <div class="mx-auto w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600">
                <flux:icon icon="{{ $invoice->gateway == 'stripe' ? 'credit-card' : 'currency-dollar' }}" size="lg" />
            </div>

            <div>
                <flux:heading size="xl">Payment Simulation</flux:heading>
                <flux:subheading class="mt-2">
                    You are paying <strong>{{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}</strong> via <strong>{{ ucfirst($invoice->gateway) }}</strong>.
                </flux:subheading>
            </div>

            <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700 text-xs text-zinc-500">
                This is a mock payment gateway for demonstration purposes. In a production environment, you would be redirected to the actual {{ ucfirst($invoice->gateway) }} checkout page.
            </div>

            <div class="grid grid-cols-2 gap-4 pt-4">
                <flux:button href="{{ route('client.credits.callback', ['uuid' => $invoice->uuid, 'success' => 0]) }}" variant="ghost">
                    Simulate Failure
                </flux:button>
                <flux:button href="{{ route('client.credits.callback', ['uuid' => $invoice->uuid, 'success' => 1]) }}" variant="primary">
                    Simulate Success
                </flux:button>
            </div>

            <flux:text size="xs" variant="subtle">
                Transaction ID: <span class="font-mono">{{ $invoice->uuid }}</span>
            </flux:text>
        </flux:card>
    </div>
</x-layouts::app>
