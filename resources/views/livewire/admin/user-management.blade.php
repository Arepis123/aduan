<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">User Management</h1>
            <flux:button wire:click="openModal" variant="primary">
                Add User
            </flux:button>
        </div>

        <!-- Search -->
        <div class="mb-6">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search users..."
                icon="magnifying-glass"
                class="max-w-md"
            />
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span @class([
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    'bg-purple-100 text-purple-800' => $user->role === 'admin',
                                    'bg-blue-100 text-blue-800' => $user->role === 'agent',
                                ])>
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->department?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button wire:click="openModal({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    Edit
                                </button>
                                @if($user->id !== auth()->id())
                                    <button wire:click="delete({{ $user->id }})" wire:confirm="Are you sure you want to delete this user?" class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal -->
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                {{ $editingUser ? 'Edit User' : 'Add New User' }}
            </h2>

            <form wire:submit="save" class="space-y-4">
                <flux:input
                    wire:model="name"
                    label="Name"
                    placeholder="Enter name"
                    required
                />

                <flux:input
                    wire:model="email"
                    type="email"
                    label="Email"
                    placeholder="Enter email"
                    required
                />

                <flux:input
                    wire:model="password"
                    type="password"
                    label="{{ $editingUser ? 'Password (leave blank to keep current)' : 'Password' }}"
                    placeholder="Enter password"
                    :required="!$editingUser"
                />

                <flux:select
                    wire:model="role"
                    label="Role"
                    required
                >
                    <flux:select.option value="agent">Agent</flux:select.option>
                    <flux:select.option value="admin">Admin</flux:select.option>
                </flux:select>

                <flux:select
                    wire:model="department_id"
                    label="Department"
                >
                    <flux:select.option value="">No Department</flux:select.option>
                    @foreach($departments as $dept)
                        <flux:select.option value="{{ $dept->id }}">{{ $dept->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model="phone"
                    label="Phone"
                    placeholder="Enter phone number"
                />

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
