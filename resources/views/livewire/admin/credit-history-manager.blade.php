<div class="space-y-6">
    <header class="flex justify-between items-end gap-4">
        <div>
            <flux:heading size="xl" level="1">Credit History</flux:heading>
            <flux:subheading>Monitor all financial transactions and credit adjustments across the system.</flux:subheading>
        </div>
    </header>

    <flux:separator variant="subtle" />

    <flux:card class="p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search by ID, user, or reason..." />
            </div>
            
            <flux:select wire:model.live="filterType" placeholder="All Types">
                <flux:select.option value="">All Types</flux:select.option>
                <flux:select.option value="credit">Credit</flux:select.option>
                <flux:select.option value="debit">Debit</flux:select.option>
                <flux:select.option value="reversal">Reversal</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="filterUser" placeholder="All Users">
                <flux:select.option value="">All Users</flux:select.option>
                @foreach($users as $user)
                    <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterCurrency" placeholder="All Currencies">
                <flux:select.option value="">All Currencies</flux:select.option>
                <flux:select.option value="USD">USD</flux:select.option>
                <flux:select.option value="EUR">EUR</flux:select.option>
                <flux:select.option value="IDR">IDR</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="filterActor" placeholder="All Actors">
                <flux:select.option value="">All Actors</flux:select.option>
                @foreach($actors as $actor)
                    <flux:select.option value="{{ $actor->id }}">{{ $actor->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input type="date" wire:model.live="dateFrom" label="From" />
            <flux:input type="date" wire:model.live="dateTo" label="To" />
        </div>
    </flux:card>

    <flux:card p="0" class="overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>ID</flux:table.column>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Amount</flux:table.column>
                <flux:table.column>Balance Flow</flux:table.column>
                <flux:table.column>Type & Reason</flux:table.column>
                <flux:table.column>Actor</flux:table.column>
                <flux:table.column>Date</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($transactions as $transaction)
                    <flux:table.row :key="$transaction->id">
                        <flux:table.cell>
                            <span class="text-xs font-mono text-zinc-500">#{{ $transaction->id }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:profile :initials="$transaction->user->initials()" size="xs" />
                                <div class="text-sm font-medium">{{ $transaction->user->name }}</div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <span class="font-bold {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }} {{ $transaction->currency_code }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-xs text-zinc-500">
                                {{ number_format($transaction->balance_before, 2) }} &rarr; {{ number_format($transaction->balance_after, 2) }}
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-col">
                                @php
                                    $color = match($transaction->type) {
                                        'credit' => 'green',
                                        'debit' => 'zinc',
                                        'reversal' => 'orange',
                                        'topup' => 'blue',
                                        default => 'zinc'
                                    };
                                    $statusColor = match($transaction->status) {
                                        'paid' => 'green',
                                        'pending' => 'yellow',
                                        'failed' => 'red',
                                        default => 'zinc'
                                    };
                                @endphp
                                <div class="flex items-center gap-1">
                                    <flux:badge size="sm" :color="$color" variant="subtle" class="capitalize">{{ $transaction->type }}</flux:badge>
                                    @if($transaction->status !== 'paid')
                                        <flux:badge size="sm" :color="$statusColor" variant="solid" class="capitalize">{{ $transaction->status }}</flux:badge>
                                    @endif
                                    @if($transaction->reference_id)
                                        <flux:tooltip content="Reverses transaction #{{ $transaction->reference_id }}">
                                            <flux:icon icon="arrow-path" size="xs" class="text-orange-500" />
                                        </flux:tooltip>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-600 mt-1 truncate max-w-[200px]" title="{{ $transaction->reason }}">
                                    {{ $transaction->reason }}
                                    @if($transaction->payment_gateway)
                                        <span class="text-zinc-400">({{ ucfirst($transaction->payment_gateway) }})</span>
                                    @endif
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($transaction->actor)
                                <flux:badge size="sm" variant="ghost" icon="shield-check">{{ $transaction->actor->name }}</flux:badge>
                            @else
                                <flux:badge size="sm" variant="ghost" icon="cpu-chip">System</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-xs text-zinc-500">{{ $transaction->created_at->format('d M Y H:i') }}</div>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            @if($transaction->type !== 'reversal' && !App\Models\CreditTransaction::where('reference_id', $transaction->id)->exists())
                                <flux:button variant="ghost" size="sm" icon="x-mark" wire:click="confirmReverse({{ $transaction->id }})">Remove Credit</flux:button>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center text-zinc-500 py-12">
                            No transactions found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>

    <!-- Reversal Modal -->
    <flux:modal wire:model="showReverseModal" class="md:w-[450px]">
        <div class="mb-6">
            <flux:heading size="lg">Reverse Transaction?</flux:heading>
            <flux:subheading>This will create a compensating transaction to undo the selected action. The ledger remains immutable.</flux:subheading>
        </div>

        <form wire:submit="reverseTransaction" class="space-y-4">
            @error('reversal')
                <div class="p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-100 mb-4">
                    {{ $message }}
                </div>
            @enderror

            <flux:field>
                <flux:label>Reason for Reversal (Optional)</flux:label>
                <flux:textarea wire:model="reversalReason" placeholder="Correction of error, customer request, etc." />
                <flux:error name="reversalReason" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button type="button" variant="ghost" wire:click="$set('showReverseModal', false)">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Confirm Reversal</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
