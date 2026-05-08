<div class="space-y-6">
    <header class="flex justify-between items-end gap-4">
        <div>
            <flux:heading size="xl" level="1">API Keys</flux:heading>
            <flux:subheading>Manage API access keys for administrative purposes.</flux:subheading>
        </div>
        <div class="w-full md:w-auto flex gap-3">
            <flux:button variant="primary" wire:click="openModal" icon="key" class="shrink-0">Create Key</flux:button>
        </div>
    </header>

    <flux:separator variant="subtle" />

    <flux:card p="0" class="overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Description</flux:table.column>
                <flux:table.column>Requests</flux:table.column>
                <flux:table.column>Last Activity</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Created</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($keys as $key)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $key->description }}</div>
                            <div class="font-mono text-zinc-400 text-[10px] mt-1">{{ substr($key->id, 0, 8) }}...</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" inset="top bottom" color="zinc">{{ number_format($key->request_count) }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($key->last_used_at)
                                <div class="text-xs text-zinc-900 dark:text-white">{{ $key->last_used_at->diffForHumans() }}</div>
                                <div class="text-[10px] text-zinc-500">{{ $key->last_used_ip }}</div>
                            @else
                                <div class="text-xs text-zinc-400 italic">Never used</div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($key->expires_at && $key->expires_at->isPast())
                                <flux:badge size="sm" color="red">Expired</flux:badge>
                            @else
                                <flux:badge size="sm" color="green">Active</flux:badge>
                                @if($key->expires_at)
                                    <div class="text-[10px] text-zinc-500 mt-1">Exp: {{ $key->expires_at->format('M d, Y') }}</div>
                                @else
                                    <div class="text-[10px] text-zinc-500 mt-1">Never expires</div>
                                @endif
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-xs text-zinc-500">{{ $key->created_at->format('M d, Y') }}</div>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:dropdown align="end">
                                <flux:button variant="ghost" icon="ellipsis-horizontal" size="sm" inset="top bottom" />
                                <flux:menu>
                                    <flux:menu.item wire:click="revealKey('{{ $key->id }}')" icon="eye">Reveal Key</flux:menu.item>
                                    <flux:menu.item wire:click="showUsage('{{ $key->id }}')" icon="chart-bar">Usage Stats</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="confirmDelete('{{ $key->id }}')" icon="trash" variant="danger">Revoke</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-500 py-8">
                            No API keys found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <!-- Create API Key Modal -->
    <flux:modal wire:model="showModal" class="md:w-[500px]">
        <div class="mb-6">
            <flux:heading size="lg">Create API Key</flux:heading>
            <flux:subheading>Generate a new API key for system access.</flux:subheading>
        </div>

        @if($newToken)
            <div class="space-y-4">
                <flux:toast variant="success" heading="API Key" text="You can reveal this key again later if needed." class="mb-4" />
                
                <div class="relative group">
                    <div class="bg-zinc-100 dark:bg-zinc-800 p-4 pr-12 rounded-xl border border-zinc-200 dark:border-zinc-700 select-all overflow-hidden">
                        <p class="font-mono text-sm break-all text-zinc-900 dark:text-zinc-100">{{ $newToken }}</p>
                    </div>
                    <button 
                        type="button" 
                        class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                        x-on:click="navigator.clipboard.writeText('{{ $newToken }}'); $flux.toast('Copied to clipboard', 'success')"
                    >
                        <flux:icon icon="clipboard-document" size="sm" />
                    </button>
                </div>
                
                <div class="flex justify-end pt-4">
                    <flux:button variant="primary" wire:click="closeToken">Close</flux:button>
                </div>
            </div>
        @else
            <form wire:submit="createKey" class="space-y-4">
                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:input wire:model="description" placeholder="e.g. Zapier Integration" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Expiration (Optional)</flux:label>
                    <flux:input type="datetime-local" wire:model="expiresAt" />
                    <flux:description>Leave empty for a key that never expires.</flux:description>
                    <flux:error name="expiresAt" />
                </flux:field>

                <div class="flex justify-end gap-2 pt-4">
                    <flux:button type="button" variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Generate Key</flux:button>
                </div>
            </form>
        @endif
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal wire:model="showDeleteModal" class="md:w-[28rem]">
        <form wire:submit="deleteKey" class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-red-500">Revoke API Key?</flux:heading>
                <flux:subheading class="mt-2">
                    <p>Any application using this API key will immediately lose access. This cannot be undone.</p>
                    <p class="mt-2">Please type <strong class="text-zinc-900 dark:text-white font-mono bg-zinc-100 dark:bg-zinc-800 px-1 py-0.5 rounded">{{ $deleteTargetExpected }}</strong> to confirm.</p>
                </flux:subheading>
            </div>

            <flux:field>
                <flux:input wire:model.live="deleteVerificationInput" placeholder="{{ $deleteTargetExpected }}" autocomplete="off" />
                <flux:error name="deleteVerificationInput" />
            </flux:field>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button wire:click="$set('showDeleteModal', false)" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="danger" x-bind:disabled="$wire.deleteVerificationInput !== $wire.deleteTargetExpected">
                    Revoke Key
                </flux:button>
            </div>
        </form>
    </flux:modal>
    <!-- Usage Stats Modal -->
    <flux:modal wire:model="showUsageModal" class="md:w-[600px]">
        @if($usageStats)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Usage Statistics</flux:heading>
                    <flux:subheading>{{ $usageStats->description }}</flux:subheading>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800">
                        <div class="text-[10px] uppercase tracking-wider text-zinc-500 font-bold mb-1">Total Requests</div>
                        <div class="text-xl font-bold">{{ number_format($usageStats->request_count) }}</div>
                    </div>
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800 col-span-2">
                        <div class="text-[10px] uppercase tracking-wider text-zinc-500 font-bold mb-1">Last Active</div>
                        <div class="text-sm font-medium">
                            @if($usageStats->last_used_at)
                                {{ $usageStats->last_used_at->format('M d, Y H:i:s') }}
                                <span class="text-zinc-400 font-normal">({{ $usageStats->last_used_ip }})</span>
                            @else
                                Never used
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <flux:heading size="sm" class="mb-3">Recent Request History</flux:heading>
                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Time</flux:table.column>
                                <flux:table.column>IP / Method</flux:table.column>
                                <flux:table.column>Endpoint</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach($recentLogs as $log)
                                    <flux:table.row>
                                        <flux:table.cell class="text-[10px]">{{ $log->created_at->format('H:i:s') }}</flux:table.cell>
                                        <flux:table.cell>
                                            <div class="text-xs font-medium">{{ $log->ip_address }}</div>
                                            <flux:badge size="xs" color="blue" variant="subtle" class="mt-1 uppercase">{{ $log->method }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <div class="text-[10px] font-mono text-zinc-500 break-all max-w-[200px]">{{ Str::after($log->endpoint, '/api/') }}</div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </div>

                <div class="flex justify-end">
                    <flux:button wire:click="$set('showUsageModal', false)">Close</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
