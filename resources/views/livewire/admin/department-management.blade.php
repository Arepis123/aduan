<div class="space-y-6">
    <div class="flex justify-between items-center">
        <flux:heading size="xl">Department Management</flux:heading>
        <flux:button wire:click="openModal" variant="primary" icon="plus">
            Add Department
        </flux:button>
    </div>

    <!-- Departments Table -->
    <flux:card class="p-4 sm:p-6 dark:bg-zinc-900 rounded-lg">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>No</flux:table.column>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Sector</flux:table.column>
                <flux:table.column>Person(s) In Charge</flux:table.column>
                <flux:table.column>Units</flux:table.column>
                <flux:table.column>Users</flux:table.column>
                <flux:table.column>Tickets</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($departments as $department)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">
                            {{ $loop->iteration }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <div>
                                <flux:text class="font-medium">{{ $department->name }}</flux:text>
                                @if($department->description)
                                    <flux:text size="xs" class="text-zinc-500 truncate max-w-xs">{{ $department->description }}</flux:text>
                                @endif
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($department->sector)
                                <flux:badge size="sm" color="amber">{{ $department->sector->name }}</flux:badge>
                            @else
                                <flux:text size="sm" class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($department->emails && count($department->emails) > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($department->emails as $email)
                                        <flux:badge size="sm" color="sky">{{ $email }}</flux:badge>
                                    @endforeach
                                </div>
                            @else
                                <flux:text size="sm" class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $department->units_count }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $department->users_count }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $department->tickets_count }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :color="$department->is_active ? 'green' : 'zinc'"
                            >
                                {{ $department->is_active ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex gap-2">
                                <flux:button wire:click="openModal({{ $department->id }})" variant="ghost" size="sm" icon="pencil" />
                                <flux:button wire:click="delete({{ $department->id }})" wire:confirm="Are you sure you want to delete this department?" variant="ghost" size="sm" icon="trash" class="text-red-600 hover:text-red-800" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="text-center py-8">
                            <flux:text>No departments found. Click "Add Department" to create one.</flux:text>
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
                {{ $editingDepartment ? 'Edit Department' : 'Add New Department' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:field>
                    <flux:label>Sector</flux:label>
                    <flux:select wire:model="sector_id" placeholder="Select a sector (optional)">
                        <flux:select.option value="">No Sector</flux:select.option>
                        @foreach($sectors as $sector)
                            <flux:select.option value="{{ $sector->id }}">{{ $sector->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:description>Group this department under a sector</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label badge="Required">Name</flux:label>
                    <flux:input wire:model="name" placeholder="Enter department name" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea wire:model="description" placeholder="Enter department description" rows="3" />
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
                    <flux:description>Add email addresses for receiving ticket notifications</flux:description>

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
                        {{ $editingDepartment ? 'Update' : 'Create' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
