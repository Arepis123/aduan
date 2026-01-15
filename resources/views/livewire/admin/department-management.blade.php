<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Department Management</h1>
            <flux:button wire:click="openModal" variant="primary">
                Add Department
            </flux:button>
        </div>

        <!-- Departments Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($departments as $department)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $department->name }}</h3>
                            @if($department->email)
                                <p class="text-sm text-gray-500">{{ $department->email }}</p>
                            @endif
                        </div>
                        <span @class([
                            'px-2 py-1 text-xs font-medium rounded-full',
                            'bg-green-100 text-green-800' => $department->is_active,
                            'bg-gray-100 text-gray-800' => !$department->is_active,
                        ])>
                            {{ $department->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    @if($department->description)
                        <p class="text-sm text-gray-600 mb-4">{{ $department->description }}</p>
                    @endif

                    <div class="grid grid-cols-3 gap-4 mb-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $department->users_count }}</p>
                            <p class="text-xs text-gray-500">Users</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $department->categories_count }}</p>
                            <p class="text-xs text-gray-500">Categories</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $department->tickets_count }}</p>
                            <p class="text-xs text-gray-500">Tickets</p>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-4 border-t border-gray-200">
                        <button wire:click="openModal({{ $department->id }})" class="flex-1 text-center text-sm text-indigo-600 hover:text-indigo-900">
                            Edit
                        </button>
                        <button wire:click="delete({{ $department->id }})" wire:confirm="Are you sure you want to delete this department?" class="flex-1 text-center text-sm text-red-600 hover:text-red-900">
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-lg shadow p-12 text-center text-gray-500">
                    No departments found. Click "Add Department" to create one.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal -->
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                {{ $editingDepartment ? 'Edit Department' : 'Add New Department' }}
            </h2>

            <form wire:submit="save" class="space-y-4">
                <flux:input
                    wire:model="name"
                    label="Name"
                    placeholder="Enter department name"
                    required
                />

                <flux:textarea
                    wire:model="description"
                    label="Description"
                    placeholder="Enter department description"
                    rows="3"
                />

                <flux:input
                    wire:model="email"
                    type="email"
                    label="Email"
                    placeholder="Enter department email for notifications"
                />

                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">Active</span>
                </label>

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
