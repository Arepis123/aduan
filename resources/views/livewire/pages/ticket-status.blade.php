<div class="max-w-4xl mx-auto space-y-6">
    <!-- Ticket Header -->
    <flux:card>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <flux:text size="sm" class="mb-1">Ticket Number</flux:text>
                <flux:heading size="xl">{{ $ticket->ticket_number }}</flux:heading>
            </div>
            <div class="flex items-center gap-3">
                <!-- Priority Badge -->
                <flux:badge
                    :color="match($ticket->priority) {
                        'low' => 'green',
                        'medium' => 'yellow',
                        'high' => 'orange',
                        'urgent' => 'red',
                        default => 'zinc'
                    }"
                >
                    {{ ucfirst($ticket->priority) }} Priority
                </flux:badge>
                <!-- Status Badge -->
                <flux:badge
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
            </div>
        </div>

        <flux:heading size="lg" class="mb-4">{{ $ticket->subject }}</flux:heading>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="flex gap-2">
                <flux:text size="sm">Department:</flux:text>
                <flux:text size="sm" class="font-medium">{{ $ticket->department?->name ?? 'N/A' }}</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:text size="sm">Category:</flux:text>
                <flux:text size="sm" class="font-medium">{{ $ticket->category?->name ?? 'N/A' }}</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:text size="sm">Submitted:</flux:text>
                <flux:text size="sm" class="font-medium">{{ $ticket->created_at->format('M d, Y h:i A') }}</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:text size="sm">Assigned To:</flux:text>
                <flux:text size="sm" class="font-medium">{{ $ticket->assignedAgent?->name ?? 'Unassigned' }}</flux:text>
            </div>
        </div>
    </flux:card>

    <!-- Original Description -->
    <flux:card>
        <flux:heading size="lg" class="mb-4">Description</flux:heading>
        <div class="prose prose-sm dark:prose-invert max-w-none">
            {!! nl2br(e($ticket->description)) !!}
        </div>

        @if($ticket->attachments->where('ticket_reply_id', null)->count() > 0)
            <flux:separator class="my-6" />
            <flux:heading size="sm" class="mb-3">Attachments</flux:heading>
            <div class="flex flex-wrap gap-2">
                @foreach($ticket->attachments->where('ticket_reply_id', null) as $attachment)
                    <flux:button
                        href="{{ $attachment->getPublicUrl($ticket->ticket_number) }}"
                        target="_blank"
                        variant="ghost"
                        size="sm"
                        icon="paper-clip"
                    >
                        {{ $attachment->original_filename }}
                    </flux:button>
                @endforeach
            </div>
        @endif
    </flux:card>

    <!-- Conversation -->
    <flux:card>
        <flux:heading size="lg" class="mb-4">Conversation</flux:heading>

        @if($ticket->publicReplies->count() > 0)
            <div class="space-y-4">
                @foreach($ticket->publicReplies as $reply)
                    <div @class([
                        'p-4 rounded-lg',
                        'bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800' => !$reply->is_client_reply,
                        'bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700' => $reply->is_client_reply,
                    ])>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <flux:text class="font-medium">{{ $reply->author_name }}</flux:text>
                                @if(!$reply->is_client_reply)
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
            <flux:text class="text-center py-4">No replies yet. Our team will respond shortly.</flux:text>
        @endif

        <!-- Reply Form -->
        @if($ticket->isOpen() || $showReplyForm)
            <flux:separator class="my-6" />

            <!-- Form-level errors -->
            @error('form')
                <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">
                    <flux:callout.heading>Error</flux:callout.heading>
                    <flux:callout.text>{{ $message }}</flux:callout.text>
                </flux:callout>
            @enderror

            <div class="space-y-4">
                <!-- Honeypot fields (hidden from real users) -->
                <div class="hidden" aria-hidden="true">
                    <input type="text" name="website" wire:model="website" tabindex="-1" autocomplete="off">
                    <input type="hidden" name="honeypot_time" wire:model="honeypot_time">
                </div>

                <flux:field>
                    <flux:label>Add a Reply</flux:label>
                    <flux:textarea
                        wire:model="reply"
                        placeholder="Type your message here"
                        rows="4"
                    />
                    <flux:description>Minimum 10 characters</flux:description>
                    <flux:error name="reply" />
                </flux:field>

                <div class="flex justify-end">
                    <flux:button
                        type="button"
                        variant="primary"
                        icon="paper-airplane"
                        wire:click="validateReply"
                        wire:loading.attr="disabled"
                        wire:target="validateReply"
                    >
                        <span wire:loading.remove wire:target="validateReply">Send Reply</span>
                        <span wire:loading wire:target="validateReply">Validating...</span>
                    </flux:button>
                </div>
            </div>
        @else
            <flux:separator class="my-6" />
            <div class="text-center">
                <flux:text class="mb-4">This ticket has been {{ $ticket->status }}.</flux:text>
                <flux:button
                    wire:click="$set('showReplyForm', true)"
                    variant="ghost"
                >
                    Need to add more information? Click here to reopen
                </flux:button>
            </div>
        @endif
    </flux:card>

    <!-- Back Link -->
    <div class="text-center">
        <flux:button href="{{ route('check') }}" variant="ghost" icon="arrow-left" wire:navigate>
            Check another ticket
        </flux:button>
    </div>

    <!-- Security Verification Modal for Reply -->
    <flux:modal name="reply-captcha-modal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Security Verification</flux:heading>
                <flux:subheading>Please solve this simple math problem to verify you're human.</flux:subheading>
            </div>

            <div class="flex items-center justify-center py-4">
                <div class="text-center">
                    <flux:text class="text-lg mb-2">What is</flux:text>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">{{ $captchaQuestion }}</p>
                </div>
            </div>

            <flux:field>
                <flux:label>Your Answer</flux:label>
                <flux:input
                    wire:model="captchaAnswer"
                    type="number"
                    placeholder="Enter the answer"
                    autofocus
                />
                <flux:error name="captchaAnswer" />
            </flux:field>

            <div class="flex justify-between items-center">
                <flux:button
                    type="button"
                    variant="ghost"
                    size="sm"
                    icon="arrow-path"
                    wire:click="refreshCaptcha"
                >
                    Different Question
                </flux:button>

                <div class="flex gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button
                        variant="primary"
                        icon="paper-airplane"
                        wire:click="submitReply"
                        wire:loading.attr="disabled"
                        wire:target="submitReply"
                    >
                        <span wire:loading.remove wire:target="submitReply">Send</span>
                        <span wire:loading wire:target="submitReply">Sending...</span>
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
