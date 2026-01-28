<div class="max-w-3xl mx-auto">
    @if($submittedTicket)
        <!-- Success Message -->
        <flux:card class="text-center">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center mx-auto mb-4">
                <flux:icon.check class="size-8 text-green-600 dark:text-green-400" />
            </div>
            <flux:heading size="xl" class="mb-2">Ticket Submitted Successfully!</flux:heading>
            <flux:text class="mb-6">Your internal ticket has been created and logged in the system.</flux:text>

            <div class="bg-zinc-100 dark:bg-zinc-800 rounded-lg p-6 mb-6">
                <flux:text size="sm" class="mb-2">Your Ticket Number</flux:text>
                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $submittedTicket->ticket_number }}</p>
            </div>

            <div class="flex justify-center gap-4">
                <flux:button href="{{ route('staff.tickets.show', $submittedTicket) }}" variant="primary" wire:navigate>
                    View Ticket
                </flux:button>
                <flux:button href="{{ route('staff.submit') }}" wire:navigate>
                    Submit Another Ticket
                </flux:button>
            </div>
        </flux:card>
    @else
        <!-- Ticket Form -->
        <flux:card>
            <flux:heading size="xl" class="mb-2">Submit Internal Ticket</flux:heading>
            <flux:subheading class="mb-8">Submit a complaint or enquiry as a staff member.</flux:subheading>

            <form wire:submit="submit" class="space-y-6">
                <!-- Requester Information (Auto-filled) -->
                <flux:fieldset>
                    <flux:legend>Your Information</flux:legend>

                    <div class="grid md:grid-cols-2 gap-6 mt-4">
                        <flux:field>
                            <flux:label>Full Name</flux:label>
                            <flux:input value="{{ auth()->user()->name }}" disabled />
                            <flux:description>Auto-filled from your account</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>Email Address</flux:label>
                            <flux:input value="{{ auth()->user()->email }}" disabled />
                            <flux:description>Auto-filled from your account</flux:description>
                        </flux:field>
                    </div>
                </flux:fieldset>

                <flux:separator />

                <!-- Ticket Details -->
                <flux:fieldset>
                    <flux:legend>Ticket Details</flux:legend>

                    <div class="space-y-6 mt-4">
                        <flux:field>
                            <flux:label badge="Required">Subject</flux:label>
                            <flux:input wire:model="subject" placeholder="Brief description of your issue" />
                            <flux:error name="subject" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Required">Description</flux:label>
                            <flux:textarea
                                wire:model="description"
                                placeholder="Please provide detailed information about your issue or enquiry"
                                rows="5"
                            />
                            <flux:description>Minimum 20 characters</flux:description>
                            <flux:error name="description" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Priority</flux:label>
                            <flux:select wire:model="priority">
                                <flux:select.option value="low">Low - General enquiry</flux:select.option>
                                <flux:select.option value="medium">Medium - Standard issue</flux:select.option>
                                <flux:select.option value="high">High - Urgent matter</flux:select.option>
                                <flux:select.option value="urgent">Urgent - Critical issue</flux:select.option>
                            </flux:select>
                        </flux:field>
                    </div>
                </flux:fieldset>

                <flux:separator />

                <!-- Attachments -->
                <flux:fieldset>
                    <flux:legend>Attachments</flux:legend>

                    <div class="mt-4">
                        <div
                            x-data="{ dragging: false }"
                            x-on:dragover.prevent="dragging = true"
                            x-on:dragleave.prevent="dragging = false"
                            x-on:drop.prevent="dragging = false"
                            class="border-2 border-dashed rounded-lg p-6 text-center transition-colors"
                            :class="dragging ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-900/20' : 'border-zinc-300 dark:border-zinc-600'"
                        >
                            <input
                                type="file"
                                wire:model="attachments"
                                multiple
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                                class="hidden"
                                id="file-upload"
                            >
                            <label for="file-upload" class="cursor-pointer">
                                <flux:icon.arrow-up-tray class="mx-auto size-12 text-zinc-400" />
                                <flux:text class="mt-2">
                                    <span class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Click to upload</span>
                                    or drag and drop
                                </flux:text>
                                <flux:text size="sm" class="mt-1">PDF, DOC, DOCX, JPG, PNG, GIF up to 10MB each</flux:text>
                            </label>
                        </div>

                        @if(count($attachments) > 0)
                            <ul class="mt-4 space-y-2">
                                @foreach($attachments as $index => $attachment)
                                    <li class="flex items-center justify-between bg-zinc-100 dark:bg-zinc-800 rounded-lg px-4 py-2">
                                        <flux:text size="sm" class="truncate">{{ $attachment->getClientOriginalName() }}</flux:text>
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="x-mark"
                                            wire:click="removeAttachment({{ $index }})"
                                        />
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @error('attachments.*')
                            <flux:text size="sm" class="mt-2 text-red-600">{{ $message }}</flux:text>
                        @enderror
                    </div>
                </flux:fieldset>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4">
                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="paper-airplane"
                        wire:loading.attr="disabled"
                        wire:target="submit"
                    >
                        <span wire:loading.remove wire:target="submit">Submit Ticket</span>
                        <span wire:loading wire:target="submit">Submitting...</span>
                    </flux:button>
                </div>
            </form>
        </flux:card>
    @endif
</div>
