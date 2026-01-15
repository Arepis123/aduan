<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Tickets</h1>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div class="md:col-span-2">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search tickets..."
                        icon="magnifying-glass"
                    />
                </div>
                <div>
                    <flux:select wire:model.live="status">
                        <flux:select.option value="">All Status</flux:select.option>
                        <flux:select.option value="open">Open</flux:select.option>
                        <flux:select.option value="in_progress">In Progress</flux:select.option>
                        <flux:select.option value="pending">Pending</flux:select.option>
                        <flux:select.option value="resolved">Resolved</flux:select.option>
                        <flux:select.option value="closed">Closed</flux:select.option>
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model.live="priority">
                        <flux:select.option value="">All Priority</flux:select.option>
                        <flux:select.option value="low">Low</flux:select.option>
                        <flux:select.option value="medium">Medium</flux:select.option>
                        <flux:select.option value="high">High</flux:select.option>
                        <flux:select.option value="urgent">Urgent</flux:select.option>
                    </flux:select>
                </div>
                @if($isAdmin)
                    <div>
                        <flux:select wire:model.live="department_id">
                            <flux:select.option value="">All Departments</flux:select.option>
                            @foreach($departments as $dept)
                                <flux:select.option value="{{ $dept->id }}">{{ $dept->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif
                <div>
                    <flux:select wire:model.live="assigned_to">
                        <flux:select.option value="">All Agents</flux:select.option>
                        <flux:select.option value="0">Unassigned</flux:select.option>
                        @foreach($agents as $agent)
                            <flux:select.option value="{{ $agent->id }}">{{ $agent->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
            @if($search || $status || $priority || $department_id || $assigned_to)
                <div class="mt-3">
                    <button wire:click="clearFilters" class="text-sm text-indigo-600 hover:text-indigo-800">
                        Clear all filters
                    </button>
                </div>
            @endif
        </div>

        <!-- Tickets Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th wire:click="sortBy('ticket_number')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
                                <div class="flex items-center gap-1">
                                    Ticket #
                                    @if($sortBy === 'ticket_number')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requester</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th wire:click="sortBy('priority')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
                                <div class="flex items-center gap-1">
                                    Priority
                                    @if($sortBy === 'priority')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned</th>
                            <th wire:click="sortBy('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
                                <div class="flex items-center gap-1">
                                    Created
                                    @if($sortBy === 'created_at')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
                                        </svg>
                                    @endif
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-gray-50 cursor-pointer" wire:click="$navigate('{{ route('staff.tickets.show', $ticket) }}')">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                    {{ $ticket->ticket_number }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                    {{ $ticket->subject }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $ticket->requester_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $ticket->requester_email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ticket->department?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        'bg-blue-100 text-blue-800' => $ticket->status === 'open',
                                        'bg-yellow-100 text-yellow-800' => $ticket->status === 'in_progress',
                                        'bg-orange-100 text-orange-800' => $ticket->status === 'pending',
                                        'bg-green-100 text-green-800' => $ticket->status === 'resolved',
                                        'bg-gray-100 text-gray-800' => $ticket->status === 'closed',
                                    ])>
                                        {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        'bg-green-100 text-green-800' => $ticket->priority === 'low',
                                        'bg-yellow-100 text-yellow-800' => $ticket->priority === 'medium',
                                        'bg-orange-100 text-orange-800' => $ticket->priority === 'high',
                                        'bg-red-100 text-red-800' => $ticket->priority === 'urgent',
                                    ])>
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ticket->assignedAgent?->name ?? 'Unassigned' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ticket->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    No tickets found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
