<div class="max-w-4xl mx-auto">
    <!-- Ticket Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-500 mb-1">Ticket Number</p>
                <h1 class="text-2xl font-bold text-gray-900">{{ $ticket->ticket_number }}</h1>
            </div>
            <div class="flex items-center gap-3">
                <!-- Priority Badge -->
                <span @class([
                    'px-3 py-1 rounded-full text-sm font-medium',
                    'bg-green-100 text-green-800' => $ticket->priority === 'low',
                    'bg-yellow-100 text-yellow-800' => $ticket->priority === 'medium',
                    'bg-orange-100 text-orange-800' => $ticket->priority === 'high',
                    'bg-red-100 text-red-800' => $ticket->priority === 'urgent',
                ])>
                    {{ ucfirst($ticket->priority) }} Priority
                </span>
                <!-- Status Badge -->
                <span @class([
                    'px-3 py-1 rounded-full text-sm font-medium',
                    'bg-blue-100 text-blue-800' => $ticket->status === 'open',
                    'bg-yellow-100 text-yellow-800' => $ticket->status === 'in_progress',
                    'bg-orange-100 text-orange-800' => $ticket->status === 'pending',
                    'bg-green-100 text-green-800' => $ticket->status === 'resolved',
                    'bg-gray-100 text-gray-800' => $ticket->status === 'closed',
                ])>
                    {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                </span>
            </div>
        </div>

        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $ticket->subject }}</h2>

        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Department:</span>
                <span class="text-gray-900 ml-2">{{ $ticket->department?->name ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Category:</span>
                <span class="text-gray-900 ml-2">{{ $ticket->category?->name ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Submitted:</span>
                <span class="text-gray-900 ml-2">{{ $ticket->created_at->format('M d, Y h:i A') }}</span>
            </div>
            <div>
                <span class="text-gray-500">Assigned To:</span>
                <span class="text-gray-900 ml-2">{{ $ticket->assignedAgent?->name ?? 'Unassigned' }}</span>
            </div>
        </div>
    </div>

    <!-- Original Description -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
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
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Conversation -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Conversation</h3>

        @if($ticket->publicReplies->count() > 0)
            <div class="space-y-4">
                @foreach($ticket->publicReplies as $reply)
                    <div @class([
                        'p-4 rounded-lg',
                        'bg-indigo-50 border border-indigo-100' => !$reply->is_client_reply,
                        'bg-gray-50 border border-gray-200' => $reply->is_client_reply,
                    ])>
                        <div class="flex items-center justify-between mb-2">
                            <span @class([
                                'font-medium',
                                'text-indigo-900' => !$reply->is_client_reply,
                                'text-gray-900' => $reply->is_client_reply,
                            ])>
                                {{ $reply->author_name }}
                                @if(!$reply->is_client_reply)
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
            <p class="text-gray-500 text-center py-4">No replies yet. Our team will respond shortly.</p>
        @endif

        <!-- Reply Form -->
        @if($ticket->isOpen() || $showReplyForm)
            <div class="mt-6 pt-4 border-t border-gray-200">
                <form wire:submit="submitReply" class="space-y-4">
                    <flux:textarea
                        wire:model="reply"
                        label="Add a Reply"
                        placeholder="Type your message here (minimum 10 characters)"
                        rows="4"
                    />
                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary">
                            <span wire:loading.remove wire:target="submitReply">Send Reply</span>
                            <span wire:loading wire:target="submitReply">Sending...</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        @else
            <div class="mt-6 pt-4 border-t border-gray-200 text-center">
                <p class="text-gray-600 mb-4">This ticket has been {{ $ticket->status }}.</p>
                <button
                    wire:click="$set('showReplyForm', true)"
                    class="text-indigo-600 hover:text-indigo-800 font-medium"
                >
                    Need to add more information? Click here to reopen
                </button>
            </div>
        @endif
    </div>

    <!-- Back Link -->
    <div class="text-center">
        <a href="{{ route('check') }}" wire:navigate class="text-gray-600 hover:text-gray-900">
            &larr; Check another ticket
        </a>
    </div>
</div>
