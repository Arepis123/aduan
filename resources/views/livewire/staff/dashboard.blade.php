<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Dashboard</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Overview of Aduan System</p>
        </div>
        <div class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>    

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <flux:card class="!p-4">
            <flux:text size="base">Total Tickets</flux:text>
            <flux:heading size="xl" class="!text-2xl">{{ $stats['total'] }}</flux:heading>
        </flux:card>
        <flux:card class="!p-4">
            <flux:text size="base">Open</flux:text>
            <flux:heading size="xl" class="!text-2xl text-blue-600">{{ $stats['open'] }}</flux:heading>
        </flux:card>
        <flux:card class="!p-4">
            <flux:text size="base">In Progress</flux:text>
            <flux:heading size="xl" class="!text-2xl text-yellow-600">{{ $stats['in_progress'] }}</flux:heading>
        </flux:card>
        <flux:card class="!p-4">
            <flux:text size="base">Pending</flux:text>
            <flux:heading size="xl" class="!text-2xl text-orange-600">{{ $stats['pending'] }}</flux:heading>
        </flux:card>
        <flux:card class="!p-4">
            <flux:text size="base">My Assigned</flux:text>
            <flux:heading size="xl" class="!text-2xl text-indigo-600">{{ $stats['my_assigned'] }}</flux:heading>
        </flux:card>
        <flux:card class="!p-4">
            <flux:text size="base">Unassigned</flux:text>
            <flux:heading size="xl" class="!text-2xl text-red-600">{{ $stats['unassigned'] }}</flux:heading>
        </flux:card>
    </div>

    <!-- Quick Actions -->
    <div class="flex gap-4">
        <flux:button href="{{ route('staff.tickets.index') }}" variant="primary" icon="ticket" wire:navigate>
            View All Tickets
        </flux:button>
        @if($isAdmin)
            <flux:button href="{{ route('staff.users.index') }}" icon="users" wire:navigate>
                Manage Users
            </flux:button>
        @endif
    </div>

    <!-- Ticket Tracker -->
    <flux:card class="p-4 sm:p-6 dark:bg-zinc-900 rounded-lg">
        <flux:heading size="lg" class="mb-4">Ticket Tracker</flux:heading>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Ticket #</flux:table.column>
                <flux:table.column>Subject</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Assigned To</flux:table.column>
                <flux:table.column>Assigned Date</flux:table.column>
                <flux:table.column>Days Left</flux:table.column>
                <flux:table.column>Progress</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($slaTickets as $ticket)
                    @php
                        $daysRemaining = $ticket->days_remaining;
                        $isOverdue = $ticket->is_overdue;
                        $daysUsed = 7 - max(0, $daysRemaining ?? 0);
                        if ($isOverdue) $daysUsed = 7;
                        $progressPercent = round(($daysUsed / 7) * 100);
                    @endphp
                    <flux:table.row class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700/50" x-on:click="Livewire.navigate('{{ route('staff.tickets.show', $ticket) }}')">
                        <flux:table.cell class="font-medium text-indigo-600 dark:text-indigo-400">
                            {{ $ticket->ticket_number }}
                        </flux:table.cell>
                        <flux:table.cell class="max-w-xs truncate">
                            {{ $ticket->subject }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$ticket->status_color">
                                {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $ticket->assignedAgent?->name ?? $ticket->unit?->name ?? $ticket->department?->name ?? 'Unassigned' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $ticket->assigned_at->format('d M Y') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($isOverdue)
                                <flux:badge size="sm" color="red">Overdue ({{ abs($daysRemaining) }}d)</flux:badge>
                            @elseif($daysRemaining <= 2)
                                <flux:badge size="sm" color="amber">{{ $daysRemaining }} {{ Str::plural('day', $daysRemaining) }}</flux:badge>
                            @else
                                <flux:badge size="sm" color="green">{{ $daysRemaining }} days</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="w-24">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-zinc-200 dark:bg-zinc-700 rounded-full h-2 overflow-hidden">
                                        <div class="h-full rounded-full {{ $isOverdue ? 'bg-red-500' : ($daysRemaining <= 2 ? 'bg-amber-500' : 'bg-green-500') }}" style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                    <span class="text-xs text-zinc-500 whitespace-nowrap">{{ $progressPercent }}%</span>
                                </div>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8">
                            <flux:text>No active SLA tickets.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <!-- Recent Tickets -->
    <flux:card class="p-4 sm:p-6 dark:bg-zinc-900 rounded-lg">
        <flux:heading size="lg" class="mb-4">Recent Tickets</flux:heading>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Ticket #</flux:table.column>
                <flux:table.column>Subject</flux:table.column>
                <flux:table.column>Requester</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Priority</flux:table.column>
                <flux:table.column>Assigned To</flux:table.column>
                <flux:table.column>Created</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($recentTickets as $ticket)
                    <flux:table.row class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700/50" x-on:click="Livewire.navigate('{{ route('staff.tickets.show', $ticket) }}')">
                        <flux:table.cell class="font-medium text-indigo-600 dark:text-indigo-400">
                            {{ $ticket->ticket_number }}
                        </flux:table.cell>
                        <flux:table.cell class="max-w-xs truncate">
                            {{ $ticket->subject }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $ticket->requester_name }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :color="match($ticket->status) {
                                    'open' => 'blue',
                                    'in_progress' => 'yellow',
                                    'pending' => 'orange',
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
                            {{ $ticket->assignedAgent?->name ?? 'Unassigned' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $ticket->created_at->diffForHumans() }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8">
                            <flux:text>No tickets found.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
