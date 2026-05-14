<div class="space-y-8">
    <header class="flex justify-between items-end gap-4">
        <div>
            <flux:heading size="xl" level="1">My Credits</flux:heading>
            <flux:subheading>Manage your wallet balance and view your billing history.</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="openTopUpModal">Add Funds</flux:button>
    </header>

    <flux:separator variant="subtle" />

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Balance Card --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-blue-700 p-6 text-white shadow-lg lg:col-span-1">
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-6">
                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                        <flux:icon icon="banknotes" size="md" />
                    </div>
                    <div class="text-xs font-medium uppercase tracking-wider text-blue-100">Available Balance</div>
                </div>
                
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-bold">${{ number_format($user->balance, 2) }}</span>
                    <span class="text-blue-100 text-sm font-medium">USD</span>
                </div>

                <div class="mt-8 pt-4 border-t border-white/10 flex justify-between items-center text-xs text-blue-100">
                    <div>Last updated: {{ now()->format('H:i') }}</div>
                    <div class="flex items-center gap-1">
                        <div class="h-1.5 w-1.5 rounded-full bg-green-400"></div>
                        Secure Ledger
                    </div>
                </div>
            </div>
            {{-- Decorative circles --}}
            <div class="absolute -right-10 -bottom-10 h-40 w-40 rounded-full bg-white/5 blur-2xl"></div>
            <div class="absolute right-4 top-4 h-16 w-16 rounded-full bg-white/10 blur-xl"></div>
        </div>

        {{-- Recent Activity Summary --}}
        <flux:card class="lg:col-span-2 p-6 flex flex-col justify-between">
            <div>
                <flux:heading size="md" class="mb-4">Recent Activity</flux:heading>
                <div class="space-y-4">
                    @forelse($recentActivity as $activity)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg {{ $activity->amount > 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                                    <flux:icon icon="{{ $activity->amount > 0 ? 'plus' : 'minus' }}" size="sm" />
                                </div>
                                <div>
                                    <div class="text-sm font-medium truncate max-w-[200px]">{{ $activity->reason }}</div>
                                    <div class="text-xs text-zinc-500">{{ $activity->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <div class="text-sm font-bold {{ $activity->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $activity->amount > 0 ? '+' : '' }}{{ number_format($activity->amount, 2) }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-zinc-500 text-sm italic">No recent activity</div>
                    @endforelse
                </div>
            </div>
            @if($recentActivity->isNotEmpty())
                <div class="mt-6 pt-4 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-400">
                    Total of {{ number_format($user->balance, 2) }} available for deployments.
                </div>
            @endif
        </flux:card>
    </div>

    {{-- Transaction History --}}
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading level="2" size="lg">Transaction History</flux:heading>
            
            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search transactions..." size="sm" class="w-64" />
                <flux:select wire:model.live="filterType" size="sm" class="w-32" placeholder="All Types">
                    <flux:select.option value="">All Types</flux:select.option>
                    <flux:select.option value="topup">Top-up</flux:select.option>
                    <flux:select.option value="debit">Usage</flux:select.option>
                    <flux:select.option value="refund">Refund</flux:select.option>
                </flux:select>
            </div>
        </div>

        <flux:card p="0" class="overflow-hidden">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Transaction ID</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column>Amount</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($transactions as $transaction)
                        <flux:table.row>
                            <flux:table.cell class="font-mono text-[10px] text-zinc-500">#{{ str_pad($transaction->id, 8, '0', STR_PAD_LEFT) }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $typeLabel = match($transaction->type) {
                                        'topup' => 'Deposit',
                                        'debit' => 'Payment',
                                        'refund' => 'Refund',
                                        'reversal' => 'Correction',
                                        default => ucfirst($transaction->type)
                                    };
                                    $typeColor = match($transaction->type) {
                                        'topup' => 'green',
                                        'debit' => 'zinc',
                                        'refund' => 'blue',
                                        'reversal' => 'orange',
                                        default => 'zinc'
                                    };
                                @endphp
                                <flux:badge :color="$typeColor" size="sm" variant="subtle">{{ $typeLabel }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="text-sm">
                                <div class="font-medium truncate max-w-[300px]" title="{{ $transaction->reason }}">{{ $transaction->reason }}</div>
                                @if($transaction->payment_gateway)
                                    <div class="text-[10px] text-zinc-400 mt-0.5">via {{ ucfirst($transaction->payment_gateway) }} (Ref: {{ $transaction->payment_reference }})</div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="font-bold {{ $transaction->amount > 0 ? 'text-green-600' : 'text-zinc-900 dark:text-white' }}">
                                    {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }} {{ $transaction->currency_code }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell class="text-xs text-zinc-500">{{ $transaction->created_at->format('d M Y H:i') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="green" size="sm" icon="check">Success</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-12 text-zinc-500">
                                <flux:icon icon="document-text" size="lg" class="mx-auto mb-2 opacity-20" />
                                <p>No transactions found for the selected filters.</p>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>

    {{-- Top Up Modal --}}
    <flux:modal wire:model="showTopUpModal" class="md:w-[500px]">
        <div class="mb-6">
            <flux:heading size="lg">Add Funds to Wallet</flux:heading>
            <flux:subheading>Choose an amount and your preferred payment method.</flux:subheading>
        </div>

        <form wire:submit="initiateTopUp" class="space-y-6">
            <flux:field>
                <flux:label>Top-up Amount (USD)</flux:label>
                <div class="grid grid-cols-4 gap-2 mb-3">
                    @foreach([10, 25, 50, 100] as $amount)
                        <button type="button" 
                                wire:click="$set('topUpAmount', {{ $amount }})" 
                                class="py-2 px-3 rounded-lg border text-sm font-medium transition-colors {{ $topUpAmount == $amount ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500' }}">
                            ${{ $amount }}
                        </button>
                    @endforeach
                </div>
                <flux:input wire:model="topUpAmount" type="number" step="0.01" min="5" max="1000" icon="currency-dollar" />
                <flux:error name="topUpAmount" />
            </flux:field>

            <flux:field>
                <flux:label>Select Payment Gateway</flux:label>
                <div class="space-y-3">
                    @foreach($gateways as $gateway)
                        <label class="relative flex items-center p-4 rounded-xl border cursor-pointer transition-all {{ $selectedGateway == $gateway['id'] ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-900/10' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' }}">
                            <input type="radio" wire:model="selectedGateway" value="{{ $gateway['id'] }}" class="sr-only">
                            <div class="flex-1 flex items-center gap-4">
                                <div class="p-2 bg-white dark:bg-zinc-800 rounded-lg shadow-sm">
                                    <flux:icon icon="{{ $gateway['icon'] }}" size="md" class="{{ $selectedGateway == $gateway['id'] ? 'text-indigo-600' : 'text-zinc-400' }}" />
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-zinc-900 dark:text-white">{{ $gateway['name'] }}</div>
                                    <div class="text-xs text-zinc-500">{{ $gateway['description'] }}</div>
                                </div>
                            </div>
                            @if($selectedGateway == $gateway['id'])
                                <div class="h-5 w-5 rounded-full bg-indigo-600 flex items-center justify-center">
                                    <flux:icon icon="check" size="xs" class="text-white" />
                                </div>
                            @endif
                        </label>
                    @endforeach
                    <flux:error name="selectedGateway" />
                </div>
            </flux:field>

            <div class="pt-4 flex gap-3">
                <flux:button variant="ghost" class="flex-1" wire:click="$set('showTopUpModal', false)">Cancel</flux:button>
                <flux:button type="submit" variant="primary" class="flex-1" wire:loading.attr="disabled">
                    <span wire:loading.remove>Proceed to Payment</span>
                    <span wire:loading>Processing...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
