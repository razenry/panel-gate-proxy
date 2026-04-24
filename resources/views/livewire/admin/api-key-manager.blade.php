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
                <flux:table.column>Key ID</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Created</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($keys as $key)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $key->description }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-mono text-zinc-500 text-xs">{{ $key->id }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($key->expires_at && $key->expires_at->isPast())
                                <flux:badge size="sm" color="red">Expired</flux:badge>
                            @else
                                <flux:badge size="sm" color="green">Active</flux:badge>
                                @if($key->expires_at)
                                    <div class="text-[10px] text-zinc-500 mt-1">Exp: {{ $key->expires_at->format('M d, Y H:i') }}</div>
                                @else
                                    <div class="text-[10px] text-zinc-500 mt-1">Never expires</div>
                                @endif
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-xs text-zinc-500">{{ $key->created_at->format('M d, Y') }}</div>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button variant="danger" icon="trash" size="sm" wire:click="confirmDelete('{{ $key->id }}')">Revoke</flux:button>
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
                <flux:toast variant="success" heading="Key Created Successfully" text="Please copy this key now. You will not be able to see it again." class="mb-4" />
                
                <div class="bg-zinc-100 dark:bg-zinc-800 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <p class="font-mono text-sm break-all text-zinc-900 dark:text-zinc-100">{{ $newToken }}</p>
                </div>
                
                <div class="flex justify-end pt-4">
                    <flux:button variant="primary" wire:click="closeToken">Done</flux:button>
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
</div>
