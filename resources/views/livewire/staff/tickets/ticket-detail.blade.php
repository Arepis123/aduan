<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <flux:button href="{{ route('staff.tickets.index') }}" variant="ghost" icon="arrow-left" wire:navigate />
        <div>
            <flux:heading size="xl">{{ $ticket->ticket_number }}</flux:heading>
            <flux:text size="sm">{{ $ticket->requester_name }} &lt;{{ $ticket->requester_email }}&gt;</flux:text>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket Details -->
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ $ticket->subject }}</flux:heading>
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    {!! nl2br(e($ticket->description)) !!}
                </div>

                @if($ticket->attachments->where('ticket_reply_id', null)->count() > 0)
                    <flux:separator class="my-6" />
                    <flux:heading size="sm" class="mb-3">Attachments</flux:heading>
                    <div class="flex flex-wrap gap-2">
                        @foreach($ticket->attachments->where('ticket_reply_id', null) as $attachment)
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

            <!-- Conversation -->
            <flux:card>
                <flux:heading size="lg" class="mb-4">Conversation</flux:heading>

                @if($ticket->replies->count() > 0)
                    <div class="space-y-4 mb-6">
                        @foreach($ticket->replies as $reply)
                            <div @class([
                                'p-4 rounded-lg',
                                'bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800' => !$reply->is_client_reply && !$reply->is_internal_note,
                                'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800' => $reply->is_internal_note,
                                'bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700' => $reply->is_client_reply,
                            ])>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <flux:text class="font-medium">{{ $reply->author_name }}</flux:text>
                                        @if($reply->is_internal_note)
                                            <flux:badge size="sm" color="amber">Internal Note</flux:badge>
                                        @elseif(!$reply->is_client_reply)
                                            <flux:badge size="sm" color="indigo">Staff</flux:badge>
                                        @endif
                                    </div>
                                    <flux:text size="xs">{{ $reply->created_at->format('M d, Y h:i A') }}</flux:text>
                                </div>
                                <div class="text-zinc-700 dark:text-zinc-300">
                                    {!! nl2br(e($reply->message)) !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <flux:text class="text-center py-4 mb-6">No replies yet.</flux:text>
                @endif

                <!-- Reply Form -->
                <flux:separator class="my-6" />
                <form wire:submit="submitReply" class="space-y-4">
                    <flux:field>
                        <flux:label>Add Reply</flux:label>
                        <flux:textarea
                            wire:model="reply"
                            placeholder="Type your response here..."
                            rows="4"
                        />
                        <flux:error name="reply" />
                    </flux:field>
                    <div class="flex items-center justify-between">
                        <flux:checkbox wire:model="isInternalNote" label="Internal note (not visible to requester)" />
                        <flux:button type="submit" variant="primary" icon="paper-airplane">
                            Send Reply
                        </flux:button>
                    </div>
                </form>
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
                                <flux:select.option value="pending">Pending</flux:select.option>
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
                        Update Assignment
                    </flux:button>
                </div>
            </flux:card>

            <!-- Details -->
            <flux:card>
                <flux:heading size="lg" class="mb-4">Details</flux:heading>

                <dl class="space-y-3">
                    <div>
                        <flux:text size="sm">Department</flux:text>
                        <flux:text class="font-medium">{{ $ticket->department?->name ?? 'Unassigned' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm">Unit</flux:text>
                        <flux:text class="font-medium">{{ $ticket->unit?->name ?? 'Unassigned' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm">Category</flux:text>
                        <flux:text class="font-medium">{{ $ticket->category?->name ?? 'N/A' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm">Requester Type</flux:text>
                        <flux:text class="font-medium">{{ ucfirst($ticket->requester_type) }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm">Phone</flux:text>
                        <flux:text class="font-medium">{{ $ticket->requester_phone ?? 'N/A' }}</flux:text>
                    </div>
                    <flux:separator />
                    <div>
                        <flux:text size="sm">Created</flux:text>
                        <flux:text class="font-medium">{{ $ticket->created_at->format('M d, Y h:i A') }}</flux:text>
                    </div>
                    @if($ticket->resolved_at)
                        <div>
                            <flux:text size="sm">Resolved</flux:text>
                            <flux:text class="font-medium">{{ $ticket->resolved_at->format('M d, Y h:i A') }}</flux:text>
                        </div>
                    @endif
                    @if($ticket->closed_at)
                        <div>
                            <flux:text size="sm">Closed</flux:text>
                            <flux:text class="font-medium">{{ $ticket->closed_at->format('M d, Y h:i A') }}</flux:text>
                        </div>
                    @endif
                </dl>
            </flux:card>
        </div>
    </div>
</div>
