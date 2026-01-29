<div class="space-y-6">
    <flux:heading size="xl">Tickets</flux:heading>

    <!-- Filters -->
    <flux:card class="!p-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search tickets..."
                    icon="magnifying-glass"
                />
            </div>
            <flux:select wire:model.live="status" placeholder="All Status">
                <flux:select.option value="">All Status</flux:select.option>
                <flux:select.option value="open">Open</flux:select.option>
                <flux:select.option value="in_progress">In Progress</flux:select.option>
                <flux:select.option value="resolved">Resolved</flux:select.option>
                <flux:select.option value="closed">Closed</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="priority" placeholder="All Priority">
                <flux:select.option value="">All Priority</flux:select.option>
                <flux:select.option value="low">Low</flux:select.option>
                <flux:select.option value="medium">Medium</flux:select.option>
                <flux:select.option value="high">High</flux:select.option>
                <flux:select.option value="urgent">Urgent</flux:select.option>
            </flux:select>
            @if($isAdmin)
                <flux:select wire:model.live="department_id" placeholder="All Departments">
                    <flux:select.option value="">All Departments</flux:select.option>
                    @foreach($departments as $dept)
                        <flux:select.option value="{{ $dept->id }}">{{ $dept->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
            <flux:select wire:model.live="type" placeholder="All Types">
                <flux:select.option value="">All Types</flux:select.option>
                <flux:select.option value="external">Public</flux:select.option>
                <flux:select.option value="internal">Internal</flux:select.option>
            </flux:select>
        </div>
        @if($search || $status || $priority || $department_id || $type)
            <div class="mt-3">
                <flux:button wire:click="clearFilters" variant="ghost" size="sm">
                    Clear all filters
                </flux:button>
            </div>
        @endif
    </flux:card>

    <!-- Tickets Table -->
    <flux:card class="p-4 sm:p-6 dark:bg-zinc-900 rounded-lg">
        <flux:table>
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'ticket_number'" :direction="$sortDirection" wire:click="sortBy('ticket_number')">
                    Ticket #
                </flux:table.column>
                <flux:table.column>Subject</flux:table.column>
                <flux:table.column>Requester</flux:table.column>
                <flux:table.column>Department</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'priority'" :direction="$sortDirection" wire:click="sortBy('priority')">
                    Priority
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sortBy('created_at')">
                    Created
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($tickets as $ticket)
                    <flux:table.row class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700/50" x-on:click="Livewire.navigate('{{ route('staff.tickets.show', $ticket) }}')">
                        <flux:table.cell class="font-medium text-indigo-600 dark:text-indigo-400">
                            {{ $ticket->ticket_number }}
                        </flux:table.cell>
                        <flux:table.cell class="max-w-xs truncate">
                            {{ $ticket->subject }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <div>
                                <flux:text size="sm" class="font-medium">{{ $ticket->requester_name }}</flux:text>
                                <flux:text size="xs">{{ $ticket->requester_email }}</flux:text>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $ticket->department?->name ?? '-' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :color="$ticket->requester_type === 'internal' ? 'violet' : 'sky'"
                            >
                                {{ $ticket->requester_type === 'internal' ? 'Internal' : 'Public' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :color="match($ticket->status) {
                                    'open' => 'blue',
                                    'in_progress' => 'yellow',
                                    'resolved' => 'green',
                                    'closed' => 'zinc',
                                    default => 'zinc'
                                }"
                            >
                                {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :color="match($ticket->priority) {
                                    'low' => 'green',
                                    'medium' => 'yellow',
                                    'high' => 'orange',
                                    'urgent' => 'red',
                                    default => 'zinc'
                                }"
                            >
                                {{ ucfirst($ticket->priority) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $ticket->created_at->format('M d, Y') }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center py-8">
                            <flux:text>No tickets found.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($tickets->hasPages())
            <div class="pt-4">
                <flux:pagination :paginator="$tickets" />
            </div>
        @endif
    </flux:card>
</div>
