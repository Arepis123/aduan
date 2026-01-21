<div class="space-y-6">
    <div class="flex justify-between items-center">
        <flux:heading size="xl">Sector Management</flux:heading>
        <flux:button wire:click="openModal" variant="primary" icon="plus">
            Add Sector
        </flux:button>
    </div>

    <!-- Sectors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($sectors as $sector)
            <flux:card>
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <flux:heading size="lg">{{ $sector->name }}</flux:heading>
                    </div>
                    <flux:badge
                        size="sm"
                        :color="$sector->is_active ? 'green' : 'zinc'"
                    >
                        {{ $sector->is_active ? 'Active' : 'Inactive' }}
                    </flux:badge>
                </div>

                @if($sector->description)
                    <flux:text size="sm" class="mb-4">{{ $sector->description }}</flux:text>
                @endif

                @if($sector->emails && count($sector->emails) > 0)
                    <div class="mb-4">
                        <flux:text size="xs" class="font-medium mb-2">Person(s) In Charge:</flux:text>
                        <div class="flex flex-wrap gap-1">
                            @foreach($sector->emails as $email)
                                <flux:badge size="sm" color="sky">{{ $email }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="text-center mb-4">
                    <flux:heading size="xl">{{ $sector->departments_count }}</flux:heading>
                    <flux:text size="sm">Departments</flux:text>
                </div>

                <flux:separator class="my-4" />

                <div class="flex gap-2">
                    <flux:button wire:click="openModal({{ $sector->id }})" variant="ghost" size="sm" class="flex-1">
                        Edit
                    </flux:button>
                    <flux:button wire:click="delete({{ $sector->id }})" wire:confirm="Are you sure you want to delete this sector? This will unlink all departments from this sector." variant="ghost" size="sm" class="flex-1 text-red-600 hover:text-red-800">
                        Delete
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <flux:card class="col-span-full text-center py-12">
                <flux:text>No sectors found. Click "Add Sector" to create one.</flux:text>
            </flux:card>
        @endforelse
    </div>

    <!-- Modal -->
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $editingSector ? 'Edit Sector' : 'Add New Sector' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:field>
                    <flux:label badge="Required">Name</flux:label>
                    <flux:input wire:model="name" placeholder="Enter sector name" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea wire:model="description" placeholder="Enter sector description" rows="3" />
                </flux:field>

                <flux:field>
                    <flux:label>Person(s) In Charge</flux:label>
                    <div class="flex gap-2">
                        <flux:input wire:model="newEmail" type="email" placeholder="Enter email address" class="flex-1" wire:keydown.enter.prevent="addEmail" />
                        <flux:button type="button" wire:click="addEmail" variant="ghost" icon="plus">
                            Add
                        </flux:button>
                    </div>
                    <flux:error name="newEmail" />
                    <flux:description>Add email addresses of people responsible for this sector</flux:description>

                    @if(count($emails) > 0)
                        <div class="flex flex-wrap gap-2 mt-3">
                            @foreach($emails as $index => $email)
                                <flux:badge color="sky" class="flex items-center gap-1">
                                    {{ $email }}
                                    <button type="button" wire:click="removeEmail({{ $index }})" class="ml-1 hover:text-red-600">
                                        <flux:icon.x-mark class="size-3" />
                                    </button>
                                </flux:badge>
                            @endforeach
                        </div>
                    @endif
                </flux:field>

                <flux:checkbox wire:model="is_active" label="Active" />

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="closeModal" type="button" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingSector ? 'Update' : 'Create' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
