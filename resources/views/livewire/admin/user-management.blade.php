<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">User Management</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Manage system users and their roles</p>
        </div>           
        <flux:button wire:click="openModal" variant="primary" icon="plus">
            Add User
        </flux:button>
    </div>

    <!-- Search -->
    <div class="max-w-md">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Search users..."
            icon="magnifying-glass"
        />
    </div>

    <!-- Users Table -->
    <flux:card class="p-4 sm:p-6 dark:bg-zinc-900 rounded-lg">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>No</flux:table.column>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>Assignment</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($users as $user)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">
                            {{ $loop->iteration }}
                        </flux:table.cell>
                        <flux:table.cell class="font-medium">
                            {{ $user->name }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $user->email }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :color="$user->role === 'admin' ? 'purple' : 'blue'"
                            >
                                {{ ucfirst($user->role) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-wrap gap-1">
                                @if($user->sector)
                                    <flux:badge size="sm" color="amber">{{ $user->sector->name }}</flux:badge>
                                @endif
                                @if($user->department)
                                    <flux:badge size="sm" color="indigo">{{ $user->department->name }}</flux:badge>
                                @endif
                                @if($user->unit)
                                    <flux:badge size="sm" color="sky">{{ $user->unit->name }}</flux:badge>
                                @endif
                                @if(!$user->sector && !$user->department && !$user->unit)
                                    <flux:text size="sm" class="text-zinc-400">-</flux:text>
                                @endif
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :color="$user->is_active ? 'green' : 'zinc'"
                            >
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex gap-2">
                                <flux:button wire:click="openModal({{ $user->id }})" variant="ghost" size="sm" icon="pencil" />
                                @if($user->id !== auth()->id())
                                    <flux:button wire:click="delete({{ $user->id }})" wire:confirm="Are you sure you want to delete this user?" variant="ghost" size="sm" icon="trash" class="text-red-600 hover:text-red-800" />
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8">
                            <flux:text>No users found.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($users->hasPages())
            <div class="pt-4">
                <flux:pagination :paginator="$users" />
            </div>
        @endif
    </flux:card>

    <!-- Modal -->
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $editingUser ? 'Edit User' : 'Add New User' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:field>
                    <flux:label badge="Required">Name</flux:label>
                    <flux:input wire:model="name" placeholder="Enter name" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label badge="Required">Email</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="Enter email" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label :badge="$editingUser ? '' : 'Required'">
                        {{ $editingUser ? 'Password (leave blank to keep current)' : 'Password' }}
                    </flux:label>
                    <flux:input wire:model="password" type="password" placeholder="Enter password" />
                    <flux:error name="password" />
                </flux:field>

                <flux:field>
                    <flux:label badge="Required">Role</flux:label>
                    <flux:select variant="listbox" wire:model="role">
                        <flux:select.option value="agent">Agent</flux:select.option>
                        <flux:select.option value="admin">Admin</flux:select.option>
                    </flux:select>
                    <flux:error name="role" />
                </flux:field>

                <flux:separator />

                <flux:heading size="sm">Organization Assignment</flux:heading>

                <flux:field>
                    <flux:label>Sector</flux:label>
                    <flux:select variant="listbox" wire:model.live="sector_id">
                        <flux:select.option value="">No Sector</flux:select.option>
                        @foreach($sectors as $sector)
                            <flux:select.option value="{{ $sector->id }}">{{ $sector->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Department</flux:label>
                    <flux:select variant="listbox" wire:model.live="department_id" :disabled="!$sector_id">
                        <flux:select.option value="">No Department</flux:select.option>
                        @foreach($departments as $dept)
                            <flux:select.option value="{{ $dept->id }}">{{ $dept->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @if(!$sector_id)
                        <flux:description>Select a sector first</flux:description>
                    @endif
                </flux:field>

                <flux:field>
                    <flux:label>Unit</flux:label>
                    <flux:select variant="listbox" wire:model="unit_id" :disabled="!$department_id">
                        <flux:select.option value="">No Unit</flux:select.option>
                        @foreach($units as $unit)
                            <flux:select.option value="{{ $unit->id }}">{{ $unit->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @if(!$department_id)
                        <flux:description>Select a department first</flux:description>
                    @endif
                </flux:field>

                <flux:separator />

                <flux:field>
                    <flux:label>Phone</flux:label>
                    <flux:input wire:model="phone" placeholder="Enter phone number" />
                </flux:field>

                <flux:checkbox wire:model="is_active" label="Active" description="Inactive users cannot log in to the system" />

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="closeModal" type="button" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingUser ? 'Update' : 'Create' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
