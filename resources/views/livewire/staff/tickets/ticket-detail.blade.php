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

            <!-- Ticket Details -->
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ $ticket->subject }}</flux:heading>
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    {!! $ticket->description !!}
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

            <!-- Tracking Log -->
            <flux:card>
                <flux:heading size="lg" class="mb-6">Tracking Log</flux:heading>

                @if($ticket->logs->count() > 0)
                    <flux:timeline class="mb-6">
                        @foreach($ticket->logs as $log)
                            <flux:timeline.item>
                                <flux:timeline.indicator
                                    :color="$log->isManual() ? 'indigo' : match($log->action) {
                                        'created'          => 'blue',
                                        'assigned'         => 'violet',
                                        'status_changed'   => 'green',
                                        'priority_changed' => 'amber',
                                        'closed'           => 'zinc',
                                        default            => 'zinc',
                                    }"
                                >
                                    @if($log->isManual())
                                        <flux:icon.pencil-square variant="micro" />
                                    @elseif($log->action === 'created')
                                        <flux:icon.plus variant="micro" />
                                    @elseif($log->action === 'assigned')
                                        <flux:icon.user-plus variant="micro" />
                                    @elseif($log->action === 'status_changed')
                                        <flux:icon.arrow-path variant="micro" />
                                    @elseif($log->action === 'priority_changed')
                                        <flux:icon.arrow-trending-up variant="micro" />
                                    @elseif($log->action === 'closed')
                                        <flux:icon.lock-closed variant="micro" />
                                    @else
                                        <flux:icon.information-circle variant="micro" />
                                    @endif
                                </flux:timeline.indicator>

                                <flux:timeline.content>
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        @if($log->isManual())
                                            <flux:badge size="sm" color="indigo">Note</flux:badge>
                                            <flux:text size="sm" class="font-medium">{{ $log->user?->name ?? 'Unknown' }}</flux:text>
                                        @else
                                            <flux:badge size="sm" color="zinc">System</flux:badge>
                                        @endif
                                        <flux:text size="xs" class="text-zinc-400 ml-auto">
                                            {{ $log->created_at->format('d M Y, h:i A') }}
                                        </flux:text>
                                    </div>
                                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                                        {{ $log->description }}
                                    </flux:text>
                                </flux:timeline.content>
                            </flux:timeline.item>
                        @endforeach
                    </flux:timeline>
                @else
                    <flux:text class="text-center py-6 text-zinc-400 mb-4">No log entries yet.</flux:text>
                @endif

                <flux:separator class="my-4" />

                <!-- Manual Note Entry -->
                <div>
                    <flux:heading size="sm" class="mb-3">Add Note</flux:heading>
                    <form wire:submit="submitLog" class="space-y-3">
                        <flux:field>
                            <flux:textarea
                                wire:model="logNote"
                                placeholder="Enter a note or remark..."
                                rows="3"
                            />
                            <flux:error name="logNote" />
                        </flux:field>
                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary" size="sm" icon="pencil-square">
                                Add Note
                            </flux:button>
                        </div>
                    </form>
                </div>
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
                            <flux:select variant="listbox" wire:model="newStatus" class="flex-1">
                                @if(auth()->user()->isAdmin())
                                    <flux:select.option value="open">Open</flux:select.option>
                                @endif
                                <flux:select.option value="in_progress">In Progress</flux:select.option>
                                <flux:select.option value="resolved">Resolved</flux:select.option>
                                @if(auth()->user()->isAdmin())
                                    <flux:select.option value="closed">Closed</flux:select.option>
                                @endif
                            </flux:select>
                            <flux:button wire:click="updateStatus" size="sm">Update</flux:button>
                        </div>
                    </flux:field>

                    @if($ticket->closing_remark)
                        <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                            <flux:text size="sm" class="text-zinc-500 mb-1">Closing Remark</flux:text>
                            <flux:text class="font-medium">{{ $ticket->closing_remark }}</flux:text>
                        </div>
                    @endif

                    <flux:field>
                        <flux:label>Priority</flux:label>
                        @if(auth()->user()->isAdmin())
                            <div class="flex gap-2">
                                <flux:select variant="listbox" wire:model="newPriority" class="flex-1">
                                    <flux:select.option value="low">Low</flux:select.option>
                                    <flux:select.option value="medium">Medium</flux:select.option>
                                    <flux:select.option value="high">High</flux:select.option>
                                    <flux:select.option value="urgent">Urgent</flux:select.option>
                                </flux:select>
                                <flux:button wire:click="updatePriority" size="sm">Update</flux:button>
                            </div>
                        @else
                            <flux:badge :color="match($ticket->priority) { 'urgent' => 'red', 'high' => 'orange', 'medium' => 'yellow', 'low' => 'green', default => 'zinc' }" size="sm">
                                {{ ucfirst($ticket->priority) }}
                            </flux:badge>
                        @endif
                    </flux:field>
                </div>
            </flux:card>

            <!-- Assignment -->
            <flux:card>
                <flux:heading size="lg" class="mb-4">Assignment</flux:heading>

                <div class="space-y-4">
                    <!-- Assign to Users -->
                    <div>
                        <flux:label class="mb-2 block">Assign To</flux:label>
                        @php $selectedUsers = $users->whereIn('id', $assignUserIds); @endphp
                        @if($selectedUsers->isNotEmpty())
                            <div class="flex flex-wrap gap-1 mb-2">
                                @foreach($selectedUsers as $su)
                                    <flux:badge size="sm" color="indigo">{{ $su->name }}</flux:badge>
                                @endforeach
                            </div>
                        @else
                            <flux:text size="sm" class="text-zinc-400 mb-2">No users selected</flux:text>
                        @endif
                        <flux:button wire:click="$set('showUserModal', true)" size="sm" variant="outline" icon="users" class="w-full">
                            {{ $selectedUsers->isEmpty() ? 'Select Users' : 'Change Selection' }}
                        </flux:button>
                    </div>

                    <flux:separator />

                    <!-- CC Department -->
                    <flux:field>
                        <flux:label>CC Department</flux:label>
                        <flux:description>Department email(s) will be CC'd on the notification</flux:description>
                        <flux:select variant="listbox" wire:model="ccDepartmentId" class="mt-1">
                            <flux:select.option value="">None</flux:select.option>
                            @foreach($departments as $department)
                                <flux:select.option value="{{ $department->id }}">{{ $department->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
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
                        <flux:text size="sm" class="text-zinc-500">Assigned To</flux:text>
                        @if($ticket->assignees->count() > 0)
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($ticket->assignees as $assignee)
                                    <flux:badge size="sm" color="indigo">{{ $assignee->name }}</flux:badge>
                                @endforeach
                            </div>
                        @else
                            <flux:text class="font-medium text-zinc-400">Unassigned</flux:text>
                        @endif
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">CC Department</flux:text>
                        <flux:text class="font-medium">{{ $ticket->department?->name ?? '-' }}</flux:text>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-zinc-500">CC Sector</flux:text>
                        <flux:text class="font-medium">{{ $ticket->sector?->name ?? '-' }}</flux:text>
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

    <!-- User Picker Modal -->
    <flux:modal wire:model="showUserModal" class="max-w-md">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Select Users to Assign</flux:heading>
                <flux:text size="sm" class="text-zinc-500 mt-1">Search and select one or more users to handle this ticket.</flux:text>
            </div>

            <flux:pillbox
                wire:model="assignUserIds"
                multiple
                searchable
                search:placeholder="Search by name or email..."
                placeholder="Pick users..."
            >
                @foreach($users as $user)
                    <flux:pillbox.option :value="$user->id">
                        <div class="flex flex-col">
                            <span class="font-medium">{{ $user->name }}</span>
                            <span class="text-xs text-zinc-400">{{ $user->email }}</span>
                        </div>
                    </flux:pillbox.option>
                @endforeach
            </flux:pillbox>

            <div class="flex items-center justify-between pt-2">
                <flux:text size="sm" class="text-zinc-500">
                    {{ count($assignUserIds) }} user(s) selected
                </flux:text>
                <flux:button wire:click="$set('showUserModal', false)" variant="primary">
                    Done
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Close Ticket Modal -->
    <flux:modal wire:model="showCloseModal" class="md:w-96 space-y-6">
        <div>
            <flux:heading size="lg">Close Ticket</flux:heading>
            <flux:text class="mt-1">Please provide a closing remark before closing this ticket.</flux:text>
        </div>

        <form wire:submit="closeTicket" class="space-y-4">
            <flux:field>
                <flux:label>Closing Remark <span class="text-red-500">*</span></flux:label>
                <flux:textarea
                    wire:model="closingRemark"
                    placeholder="Enter your closing remark..."
                    rows="4"
                />
                <flux:error name="closingRemark" />
            </flux:field>

            <flux:field>
                <flux:label>Attachments (Optional)</flux:label>
                <input
                    type="file"
                    wire:model="newClosingAttachments"
                    multiple
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                    class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-zinc-300"
                />
                <flux:description>Max 5 files, 10MB each (PDF, DOC, DOCX, JPG, PNG, GIF)</flux:description>
                <flux:error name="closingAttachments" />
                <flux:error name="closingAttachments.*" />
            </flux:field>

            @if(count($closingAttachments) > 0)
                <div class="space-y-2">
                    @foreach($closingAttachments as $index => $attachment)
                        <div class="flex items-center justify-between p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                            <flux:text size="sm" class="truncate">{{ $attachment->getClientOriginalName() }}</flux:text>
                            <flux:button wire:click="removeClosingAttachment({{ $index }})" variant="ghost" size="sm" icon="x-mark" />
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex justify-end gap-2">
                <flux:button wire:click="cancelClose" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="danger">Close Ticket</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
