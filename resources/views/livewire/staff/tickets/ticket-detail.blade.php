<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <flux:button href="{{ route('staff.tickets.index') }}" variant="ghost" icon="arrow-left" wire:navigate />
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <flux:heading size="xl">{{ $ticket->ticket_number }}</flux:heading>
                <flux:badge :color="$ticket->requester_type === 'internal' ? 'violet' : 'sky'" size="sm">
                    {{ $ticket->requester_type === 'internal' ? 'Internal' : 'Public' }}
                </flux:badge>
                <flux:badge size="sm" :color="$ticket->status_color">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</flux:badge>
                <flux:badge size="sm" :color="$ticket->priority_color">{{ ucfirst($ticket->priority) }}</flux:badge>
            </div>
            <flux:text size="sm">{{ $ticket->requester_name }} &lt;{{ $ticket->requester_email }}&gt;</flux:text>
        </div>
    </div>

    <!-- Deadline Warning -->
    @if($ticket->assigned_at && $ticket->isOpen())
        <div @class([
            'p-4 rounded-lg border',
            'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800' => $ticket->is_overdue,
            'bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-800' => !$ticket->is_overdue && $ticket->days_remaining <= 2,
            'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' => !$ticket->is_overdue && $ticket->days_remaining > 2,
        ])>
            <div class="flex items-center gap-3">
                @if($ticket->is_overdue)
                    <flux:icon.exclamation-triangle class="size-5 text-red-600" />
                    <flux:text class="font-medium text-red-800 dark:text-red-200">
                        This ticket is overdue! Due date was {{ $ticket->due_date->format('d M Y') }}
                    </flux:text>
                @elseif($ticket->days_remaining <= 2)
                    <flux:icon.clock class="size-5 text-amber-600" />
                    <flux:text class="font-medium text-amber-800 dark:text-amber-200">
                        {{ $ticket->days_remaining }} day(s) remaining - Due {{ $ticket->due_date->format('d M Y') }}
                    </flux:text>
                @else
                    <flux:icon.clock class="size-5 text-green-600" />
                    <flux:text class="font-medium text-green-800 dark:text-green-200">
                        {{ $ticket->days_remaining }} days remaining - Due {{ $ticket->due_date->format('d M Y') }}
                    </flux:text>
                @endif
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket Details -->
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ $ticket->subject }}</flux:heading>
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    {!! nl2br(e($ticket->description)) !!}
                </div>

                @if($ticket->attachments->count() > 0)
                    <flux:separator class="my-6" />
                    <flux:heading size="sm" class="mb-3">Attachments</flux:heading>
                    <div class="flex flex-wrap gap-2">
                        @foreach($ticket->attachments as $attachment)
                            <flux:button
                                href="{{ $attachment->url }}"
                                target="_blank"
                                variant="ghost"
                                size="sm"
                                icon="paper-clip"
                            >
                                {{ $attachment->original_filename }} ({{ $attachment->human_size }})
                            </flux:button>
                        @endforeach
                    </div>
                @endif
            </flux:card>

            <!-- Requester Information -->
            <flux:card>
                <flux:heading size="lg" class="mb-4">Requester Information</flux:heading>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <flux:text size="sm" class="text-zinc-500">Name</flux:text>
                        <flux:text class="font-medium">{{ $ticket->requester_name }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">Email</flux:text>
                        <flux:text class="font-medium">{{ $ticket->requester_email }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">Phone</flux:text>
                        <flux:text class="font-medium">{{ $ticket->requester_phone ?? 'N/A' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">Type</flux:text>
                        <flux:text class="font-medium">{{ ucfirst($ticket->requester_type) }}</flux:text>
                    </div>
                </dl>
            </flux:card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status & Priority -->
            <flux:card>
                <flux:heading size="lg" class="mb-4">Ticket Status</flux:heading>

                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Status</flux:label>
                        <div class="flex gap-2">
                            <flux:select wire:model="newStatus" class="flex-1">
                                <flux:select.option value="open">Open</flux:select.option>
                                <flux:select.option value="in_progress">In Progress</flux:select.option>
                                <flux:select.option value="resolved">Resolved</flux:select.option>
                                <flux:select.option value="closed">Closed</flux:select.option>
                            </flux:select>
                            <flux:button wire:click="updateStatus" size="sm">Update</flux:button>
                        </div>
                    </flux:field>

                    <flux:field>
                        <flux:label>Priority</flux:label>
                        <div class="flex gap-2">
                            <flux:select wire:model="newPriority" class="flex-1">
                                <flux:select.option value="low">Low</flux:select.option>
                                <flux:select.option value="medium">Medium</flux:select.option>
                                <flux:select.option value="high">High</flux:select.option>
                                <flux:select.option value="urgent">Urgent</flux:select.option>
                            </flux:select>
                            <flux:button wire:click="updatePriority" size="sm">Update</flux:button>
                        </div>
                    </flux:field>
                </div>
            </flux:card>

            <!-- Assignment -->
            <flux:card>
                <flux:heading size="lg" class="mb-4">Assignment</flux:heading>

                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Department</flux:label>
                        <flux:select wire:model.live="assignDepartment">
                            <flux:select.option value="">Select Department</flux:select.option>
                            @foreach($departments as $department)
                                <flux:select.option value="{{ $department->id }}">{{ $department->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Unit</flux:label>
                        <flux:select wire:model="assignUnit" :disabled="!$assignDepartment">
                            <flux:select.option value="">Select Unit</flux:select.option>
                            @foreach($units as $unit)
                                <flux:select.option value="{{ $unit->id }}">{{ $unit->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @if(!$assignDepartment)
                            <flux:description>Select a department first</flux:description>
                        @endif
                    </flux:field>

                    <flux:button wire:click="updateAssignment" variant="primary" size="sm" class="w-full">
                        Assign & Send Notification
                    </flux:button>

                    @if($ticket->assigned_at)
                        <flux:text size="xs" class="text-center text-zinc-500">
                            Assigned on {{ $ticket->assigned_at->format('d M Y, h:i A') }}
                        </flux:text>
                    @endif
                </div>
            </flux:card>

            <!-- Details -->
            <flux:card>
                <flux:heading size="lg" class="mb-4">Details</flux:heading>

                <dl class="space-y-3">
                    <div>
                        <flux:text size="sm" class="text-zinc-500">Department</flux:text>
                        <flux:text class="font-medium">{{ $ticket->department?->name ?? 'Unassigned' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">Unit</flux:text>
                        <flux:text class="font-medium">{{ $ticket->unit?->name ?? 'Unassigned' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">Category</flux:text>
                        <flux:text class="font-medium">{{ $ticket->category?->name ?? 'N/A' }}</flux:text>
                    </div>
                    <flux:separator />
                    <div>
                        <flux:text size="sm" class="text-zinc-500">Created</flux:text>
                        <flux:text class="font-medium">{{ $ticket->created_at->format('d M Y, h:i A') }}</flux:text>
                    </div>
                    @if($ticket->assigned_at)
                        <div>
                            <flux:text size="sm" class="text-zinc-500">Assigned</flux:text>
                            <flux:text class="font-medium">{{ $ticket->assigned_at->format('d M Y, h:i A') }}</flux:text>
                        </div>
                    @endif
                    @if($ticket->resolved_at)
                        <div>
                            <flux:text size="sm" class="text-zinc-500">Resolved</flux:text>
                            <flux:text class="font-medium">{{ $ticket->resolved_at->format('d M Y, h:i A') }}</flux:text>
                        </div>
                    @endif
                    @if($ticket->closed_at)
                        <div>
                            <flux:text size="sm" class="text-zinc-500">Closed</flux:text>
                            <flux:text class="font-medium">{{ $ticket->closed_at->format('d M Y, h:i A') }}</flux:text>
                        </div>
                    @endif
                </dl>
            </flux:card>
        </div>
    </div>
</div>
