<div class="space-y-6">
    <header class="flex justify-between items-end gap-4">
        <div>
            <flux:heading size="xl" level="1">Subscriptions</flux:heading>
            <flux:subheading>Manage client assigned plans and expiration globally.</flux:subheading>
        </div>
        <div class="w-full md:w-auto flex gap-3">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search by name, email, or ID..." />
            <flux:button variant="primary" wire:click="openModal" icon="ticket" class="shrink-0">New Subscription</flux:button>
        </div>
    </header>

    <flux:separator variant="subtle" />

    <flux:card p="0" class="overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Client</flux:table.column>
                <flux:table.column>Plan & Quota</flux:table.column>
                <flux:table.column>Status & Expiration</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($subscriptions as $sub)
                    <flux:table.row>
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:profile :initials="$sub->user->initials()" size="sm" />
                                <div>
                                    <div class="font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                                        {{ $sub->user->name }}
                                    </div>
                                    <div class="text-xs text-zinc-500">{{ $sub->user->email }}</div>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium">{{ $sub->plan_name }}</div>
                            <div class="text-xs text-zinc-500">Limits: <span class="font-medium">{{ $sub->servers_count }}</span> / {{ $sub->max_server }} servers</div>
                            <div class="text-[10px] font-mono text-zinc-400 mt-1">{{ $sub->external_id }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($sub->isActive())
                                <flux:badge color="green" size="sm" variant="subtle">Active</flux:badge>
                            @elseif($sub->status === 'suspended')
                                <flux:badge color="yellow" size="sm" variant="subtle">Suspended</flux:badge>
                            @else
                                <flux:badge color="red" size="sm" variant="subtle">Expired</flux:badge>
                            @endif
                            @if($sub->expired_at)
                                <div class="mt-1 text-[11px] font-medium text-zinc-500">Exp: {{ $sub->expired_at->locale('id')->translatedFormat('d M Y H:i') }}</div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:dropdown align="end">
                                <flux:button variant="ghost" icon="ellipsis-horizontal" size="sm" inset="top bottom" />
                                <flux:menu>
                                    <flux:menu.item wire:click="openModal({{ $sub->id }})" icon="pencil-square">Edit Sub</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="confirmDelete({{ $sub->id }})" icon="trash" variant="danger">Revoke</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center text-zinc-500 py-8">
                            No active subscriptions found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <div class="mt-4">
        {{ $subscriptions->links() }}
    </div>

    <!-- Subscription Creation/Edit Modal -->
    <flux:modal wire:model="showModal" class="md:w-[500px]" unclosable>
        <div class="mb-6">
            <flux:heading size="lg">{{ $editingSubId ? 'Edit Subscription' : 'Create Subscription' }}</flux:heading>
            <flux:subheading>Assign proxy plans to users globally.</flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-4">
            {{-- User Search / Assignment --}}
            @if($editingSubId)
                <flux:field>
                    <flux:label>Client Account</flux:label>
                    <flux:input value="{{ $userSearch }}" disabled />
                </flux:field>
            @else
                @if($manageSubUserId)
                    <div class="flex items-center justify-between p-3 border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950/30 rounded-lg">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="check-circle" size="sm" class="text-blue-500" />
                            <span class="font-medium text-blue-700 dark:text-blue-300">{{ $userSearch }}</span>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="deselectUser" />
                    </div>
                @else
                    <flux:field>
                        <flux:label>Find Client Account</flux:label>
                        <flux:input wire:model.live.debounce.300ms="userSearch" placeholder="Type name or email..." autocomplete="off" />
                        <flux:error name="manageSubUserId" />
                        @if(!empty($foundUsers))
                            <div class="mt-2 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden bg-white dark:bg-zinc-800 shadow-sm max-h-40 overflow-y-auto">
                                @foreach($foundUsers as $fUser)
                                    <button type="button" class="w-full text-left px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:bg-zinc-100 flex flex-col items-start border-b border-zinc-100 dark:border-zinc-700 last:border-0" wire:click="selectUser({{ $fUser['id'] }}, '{{ addslashes($fUser['name']) }}')">
                                        <span class="font-medium">{{ $fUser['name'] }}</span>
                                        <span class="text-[10px] text-zinc-500">{{ $fUser['email'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @elseif(strlen($userSearch) > 1)
                            <div class="mt-2 text-xs text-zinc-500">No users found matching "{{ $userSearch }}"</div>
                        @endif
                    </flux:field>
                @endif
            @endif

            <flux:field>
                <flux:label>Select Plan</flux:label>
                <flux:select wire:model="selectedPlanId">
                    <flux:select.option value="" disabled selected>Choose a package...</flux:select.option>
                    @foreach($plans as $plan)
                        <flux:select.option value="{{ $plan->id }}">{{ $plan->name }} (Up to {{ $plan->max_server }} servers)</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="selectedPlanId" />
            </flux:field>

            <flux:field>
                <flux:label>Expiration Date</flux:label>
                <flux:input type="datetime-local" wire:model="expiredAt" />
                @if(!$editingSubId)
                    <flux:description>Leave empty to use system default (usually 30 days).</flux:description>
                @endif
                <flux:error name="expiredAt" />
            </flux:field>

            @if($editingSubId)
                <flux:field>
                    <flux:label>Status Override</flux:label>
                    <flux:select wire:model="status">
                        <flux:select.option value="active">Active</flux:select.option>
                        <flux:select.option value="suspended">Suspended</flux:select.option>
                        <flux:select.option value="expired">Expired</flux:select.option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>
            @endif

            <div class="flex justify-end gap-2 pt-4">
                <flux:button type="button" variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Save changes</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Secure Delete Modal --}}
    <flux:modal wire:model="showDeleteModal" class="md:w-[28rem]">
        <form wire:submit="executeDelete" class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-red-500">Revoke Subscription?</flux:heading>
                <flux:subheading class="mt-2">
                    <p>You're about to forcefully and permanently revoke this subscription. This cannot be undone.</p>
                    <p class="mt-2">Please type <strong class="text-zinc-900 dark:text-white font-mono bg-zinc-100 dark:bg-zinc-800 px-1 py-0.5 rounded">{{ $deleteTargetExpected }}</strong> to confirm.</p>
                </flux:subheading>
            </div>

            <flux:field>
                <flux:input wire:model.live="deleteVerificationInput" placeholder="{{ $deleteTargetExpected }}" autocomplete="off" />
                <flux:error name="deleteVerificationInput" />
            </flux:field>

            <div class="flex gap-2" x-data="{ countdown: 5 }" x-init="
                $watch('$wire.showDeleteModal', value => {
                    if (value) { countdown = 5; let i = setInterval(() => { if(countdown > 0) countdown--; else clearInterval(i); }, 1000); }
                })
            ">
                <flux:spacer />
                <flux:button wire:click="$set('showDeleteModal', false)" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="danger" x-bind:disabled="countdown > 0 || $wire.deleteVerificationInput !== $wire.deleteTargetExpected">
                    <span x-show="countdown > 0" x-text="'Wait ' + countdown + 's'"></span>
                    <span x-show="countdown === 0">Confirm Revoke</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
