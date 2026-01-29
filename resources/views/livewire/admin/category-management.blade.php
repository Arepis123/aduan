<div class="space-y-6">
    <div class="flex justify-between items-center">
        <flux:heading size="xl">Category Management</flux:heading>
        <flux:button wire:click="openModal" variant="primary" icon="plus">
            Add Category
        </flux:button>
    </div>

    <!-- Categories Table -->
    <flux:card class="p-4 sm:p-6 dark:bg-zinc-900 rounded-lg">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>No</flux:table.column>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Description</flux:table.column>
                <flux:table.column>Department</flux:table.column>
                <flux:table.column>Tickets</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($categories as $category)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">
                            {{ $loop->iteration }}
                        </flux:table.cell>
                        <flux:table.cell class="font-medium">
                            {{ $category->name }}
                        </flux:table.cell>
                        <flux:table.cell class="max-w-xs truncate">
                            {{ $category->description ?? '-' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $category->department?->name ?? '-' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $category->tickets_count }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :color="$category->is_active ? 'green' : 'zinc'"
                            >
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex gap-2">
                                <flux:button wire:click="openModal({{ $category->id }})" variant="ghost" size="sm" icon="pencil" />
                                <flux:button wire:click="delete({{ $category->id }})" wire:confirm="Are you sure you want to delete this category?" variant="ghost" size="sm" icon="trash" class="text-red-600 hover:text-red-800" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8">
                            <flux:text>No categories found. Click "Add Category" to create one.</flux:text>
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
                {{ $editingCategory ? 'Edit Category' : 'Add New Category' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:field>
                    <flux:label badge="Required">Name</flux:label>
                    <flux:input wire:model="name" placeholder="Enter category name" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea wire:model="description" placeholder="Enter category description" rows="3" />
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

                <flux:checkbox wire:model="is_active" label="Active" />

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button wire:click="closeModal" type="button" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingCategory ? 'Update' : 'Create' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
