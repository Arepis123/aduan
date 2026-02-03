<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Sector Management</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Manage sectors and their persons in charge</p>
        </div>         
        <flux:button wire:click="openModal" variant="primary" icon="plus">
            Add Sector
        </flux:button>
    </div>

    <!-- Sectors Table -->
    <flux:card class="p-4 sm:p-6 dark:bg-zinc-900 rounded-lg">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>No</flux:table.column>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Description</flux:table.column>
                <flux:table.column>Person(s) In Charge</flux:table.column>
                <flux:table.column>Departments</flux:table.column>
                <flux:table.column>Users</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($sectors as $sector)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">
                            {{ $loop->iteration }}
                        </flux:table.cell>
                        <flux:table.cell class="font-medium">
                            {{ $sector->name }}
                        </flux:table.cell>
                        <flux:table.cell class="max-w-xs truncate">
                            {{ $sector->description ?? '-' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($sector->emails && count($sector->emails) > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($sector->emails as $email)
                                        <flux:badge size="sm" color="sky">{{ $email }}</flux:badge>
                                    @endforeach
                                </div>
                            @else
                                <flux:text size="sm" class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $sector->departments_count }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $sector->users_count }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :color="$sector->is_active ? 'green' : 'zinc'"
                            >
                                {{ $sector->is_active ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex gap-2">
                                <flux:button wire:click="openModal({{ $sector->id }})" variant="ghost" size="sm" icon="pencil" />
                                <flux:button wire:click="delete({{ $sector->id }})" wire:confirm="Are you sure you want to delete this sector? This will unlink all departments from this sector." variant="ghost" size="sm" icon="trash" class="text-red-600 hover:text-red-800" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center py-8">
                            <flux:text>No sectors found. Click "Add Sector" to create one.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

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
