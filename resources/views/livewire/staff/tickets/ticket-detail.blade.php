<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('staff.tickets.index') }}" wire:navigate class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $ticket->ticket_number }}</h1>
                    <p class="text-sm text-gray-500">{{ $ticket->requester_name }} &lt;{{ $ticket->requester_email }}&gt;</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Ticket Details -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $ticket->subject }}</h2>
                    <div class="prose prose-sm max-w-none text-gray-700">
                        {!! nl2br(e($ticket->description)) !!}
                    </div>

                    @if($ticket->attachments->where('ticket_reply_id', null)->count() > 0)
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Attachments</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($ticket->attachments->where('ticket_reply_id', null) as $attachment)
                                    <a href="{{ $attachment->url }}" target="_blank"
                                       class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg text-sm text-gray-700 hover:bg-gray-100">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        {{ $attachment->original_filename }}
                                        <span class="text-gray-400">({{ $attachment->human_size }})</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Conversation -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Conversation</h3>

                    @if($ticket->replies->count() > 0)
                        <div class="space-y-4 mb-6">
                            @foreach($ticket->replies as $reply)
                                <div @class([
                                    'p-4 rounded-lg',
                                    'bg-indigo-50 border border-indigo-100' => !$reply->is_client_reply && !$reply->is_internal_note,
                                    'bg-yellow-50 border border-yellow-200' => $reply->is_internal_note,
                                    'bg-gray-50 border border-gray-200' => $reply->is_client_reply,
                                ])>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-medium text-gray-900">
                                            {{ $reply->author_name }}
                                            @if($reply->is_internal_note)
                                                <span class="text-xs text-yellow-600 ml-1">(Internal Note)</span>
                                            @elseif(!$reply->is_client_reply)
                                                <span class="text-xs text-indigo-600 ml-1">(Staff)</span>
                                            @endif
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $reply->created_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                    <div class="text-gray-700">
                                        {!! nl2br(e($reply->message)) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4 mb-6">No replies yet.</p>
                    @endif

                    <!-- Reply Form -->
                    <div class="border-t border-gray-200 pt-6">
                        <form wire:submit="submitReply" class="space-y-4">
                            <flux:textarea
                                wire:model="reply"
                                label="Add Reply"
                                placeholder="Type your response here..."
                                rows="4"
                            />
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="isInternalNote" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-gray-600">Internal note (not visible to requester)</span>
                                </label>
                                <flux:button type="submit" variant="primary">
                                    <span wire:loading.remove wire:target="submitReply">Send Reply</span>
                                    <span wire:loading wire:target="submitReply">Sending...</span>
                                </flux:button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status & Priority -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ticket Status</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
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
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <div class="flex gap-2">
                                <flux:select wire:model="newPriority" class="flex-1">
                                    <flux:select.option value="low">Low</flux:select.option>
                                    <flux:select.option value="medium">Medium</flux:select.option>
                                    <flux:select.option value="high">High</flux:select.option>
                                    <flux:select.option value="urgent">Urgent</flux:select.option>
                                </flux:select>
                                <flux:button wire:click="updatePriority" size="sm">Update</flux:button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignment -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Assignment</h3>

                    <div class="space-y-3">
                        <div class="flex gap-2">
                            <flux:select wire:model="assignTo" class="flex-1">
                                <flux:select.option value="">Unassigned</flux:select.option>
                                @foreach($agents as $agent)
                                    <flux:select.option value="{{ $agent->id }}">{{ $agent->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:button wire:click="updateAssignment" size="sm">Assign</flux:button>
                        </div>
                        @if(!$ticket->user_id)
                            <button wire:click="assignToMe" class="w-full text-sm text-indigo-600 hover:text-indigo-800">
                                Assign to me
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Details -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Details</h3>

                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Department</dt>
                            <dd class="text-gray-900 font-medium">{{ $ticket->department?->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Category</dt>
                            <dd class="text-gray-900 font-medium">{{ $ticket->category?->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Requester Type</dt>
                            <dd class="text-gray-900 font-medium">{{ ucfirst($ticket->requester_type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Phone</dt>
                            <dd class="text-gray-900 font-medium">{{ $ticket->requester_phone ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Created</dt>
                            <dd class="text-gray-900 font-medium">{{ $ticket->created_at->format('M d, Y h:i A') }}</dd>
                        </div>
                        @if($ticket->resolved_at)
                            <div>
                                <dt class="text-gray-500">Resolved</dt>
                                <dd class="text-gray-900 font-medium">{{ $ticket->resolved_at->format('M d, Y h:i A') }}</dd>
                            </div>
                        @endif
                        @if($ticket->closed_at)
                            <div>
                                <dt class="text-gray-500">Closed</dt>
                                <dd class="text-gray-900 font-medium">{{ $ticket->closed_at->format('M d, Y h:i A') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
