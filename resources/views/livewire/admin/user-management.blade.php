<div class="space-y-6">
    <div class="flex justify-between items-center">
        <flux:heading size="xl">User Management</flux:heading>
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
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>Department</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($users as $user)
                    <flux:table.row>
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
                            {{ $user->department?->name ?? '-' }}
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
                        <flux:table.cell colspan="5" class="text-center py-8">
                            <flux:text>No users found.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($users->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                {{ $users->links() }}
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
                    <flux:select wire:model="role">
                        <flux:select.option value="agent">Agent</flux:select.option>
                        <flux:select.option value="admin">Admin</flux:select.option>
                    </flux:select>
                    <flux:error name="role" />
                </flux:field>

                <flux:field>
                    <flux:label>Department</flux:label>
                    <flux:select wire:model="department_id">
                        <flux:select.option value="">No Department</flux:select.option>
                        @foreach($departments as $dept)
                            <flux:select.option value="{{ $dept->id }}">{{ $dept->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Phone</flux:label>
                    <flux:input wire:model="phone" placeholder="Enter phone number" />
                </flux:field>

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
