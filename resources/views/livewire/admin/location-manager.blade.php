<div class="space-y-6">
    <header class="flex justify-between items-end gap-4">
        <div>
            <flux:heading size="xl" level="1">Locations</flux:heading>
            <flux:subheading>Manage geographic locations for your infrastructure nodes.</flux:subheading>
        </div>
        <flux:button wire:click="openModal()" variant="primary" icon="plus" class="bg-blue-600 hover:bg-blue-500 border-0">Add Location</flux:button>
    </header>

    <flux:separator variant="subtle" />

    <flux:card p="0" class="overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Short Name</flux:table.column>
                <flux:table.column>Full Name</flux:table.column>
                <flux:table.column>Nodes Count</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach($locations as $location)
                    <flux:table.row>
                        <flux:table.cell>
                            <flux:badge variant="subtle" size="sm" class="font-mono">{{ $location->short }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="font-medium text-zinc-800 dark:text-zinc-200">
                            {{ $location->long }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" variant="outline">{{ $location->nodes()->count() }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:dropdown align="end" class="pointer-events-auto">
                                <flux:button variant="ghost" icon="ellipsis-horizontal" size="sm" />
                                <flux:menu>
                                    <flux:menu.item wire:click="openModal({{ $location->id }})" icon="pencil">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="confirmDelete({{ $location->id }})" variant="danger" icon="trash">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal wire:model="showModal" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingLocationId ? 'Edit Location' : 'Add Location' }}</flux:heading>
                <flux:subheading>Geographic point for node assignment.</flux:subheading>
            </div>

            <flux:field>
                <flux:label>Short Name (e.g. US-NY)</flux:label>
                <flux:input wire:model="short" placeholder="US-NY" />
                <flux:error name="short" />
            </flux:field>

            <flux:field>
                <flux:label>Full Name (e.g. United States, New York)</flux:label>
                <flux:input wire:model="long" placeholder="United States, New York" />
                <flux:error name="long" />
            </flux:field>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="primary" class="bg-blue-600 hover:bg-blue-500 border-0">Save Location</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Secure Delete Modal --}}
    <flux:modal wire:model="showDeleteModal" class="md:w-[28rem]">
        <form wire:submit="executeDelete" class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-red-500">Delete Location?</flux:heading>
                <flux:subheading class="mt-2">
                    <p>You're about to remove this location. This action cannot be undone.</p>
                    <p class="mt-2">Please type <strong class="text-zinc-900 dark:text-white font-mono bg-zinc-100 dark:bg-zinc-800 px-1 py-0.5 rounded">{{ $deleteLocationExpected }}</strong> to confirm.</p>
                </flux:subheading>
            </div>

            <flux:field>
                <flux:input wire:model.live="deleteVerificationInput" placeholder="{{ $deleteLocationExpected }}" autocomplete="off" />
                <flux:error name="deleteVerificationInput" />
            </flux:field>

            <div class="flex gap-2" x-data="{ countdown: 3 }" x-init="
                $watch('$wire.showDeleteModal', value => {
                    if (value) { countdown = 3; let i = setInterval(() => { if(countdown > 0) countdown--; else clearInterval(i); }, 1000); }
                })
            ">
                <flux:spacer />
                <flux:button wire:click="$set('showDeleteModal', false)" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="danger" x-bind:disabled="countdown > 0 || $wire.deleteVerificationInput !== $wire.deleteLocationExpected">
                    <span x-show="countdown > 0" x-text="'Wait ' + countdown + 's'"></span>
                    <span x-show="countdown === 0">Confirm Delete</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
