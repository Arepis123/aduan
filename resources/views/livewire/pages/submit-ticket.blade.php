<div class="max-w-3xl mx-auto">
    @if($submittedTicket)
        <!-- Success Message -->
        <flux:card class="text-center">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center mx-auto mb-4">
                <flux:icon.check class="size-8 text-green-600 dark:text-green-400" />
            </div>
            <flux:heading size="xl" class="mb-2">Ticket Submitted Successfully!</flux:heading>
            <flux:text class="mb-6">Your ticket has been received. Please save your ticket number for reference.</flux:text>

            <div class="bg-zinc-100 dark:bg-zinc-800 rounded-lg p-6 mb-6">
                <flux:text size="sm" class="mb-2">Your Ticket Number</flux:text>
                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $submittedTicket->ticket_number }}</p>
            </div>

            <flux:text size="sm" class="mb-6">
                A confirmation email has been sent to <strong>{{ $submittedTicket->requester_email }}</strong>
            </flux:text>

            <div class="flex justify-center gap-4">
                <flux:button href="{{ route('ticket.status', $submittedTicket->ticket_number) }}" variant="primary" wire:navigate>
                    View Ticket Status
                </flux:button>
                <flux:button href="{{ route('submit') }}" wire:navigate>
                    Submit Another Ticket
                </flux:button>
            </div>
        </flux:card>
    @else
        <!-- Ticket Form -->
        <flux:card class="dark:bg-zinc-900">
            <flux:heading size="xl" class="mb-2">Submit a New Ticket</flux:heading>
            <flux:subheading class="mb-8">Fill out the form below to submit your complaint or enquiry.</flux:subheading>

            <form wire:submit="submit" class="space-y-6">
                <!-- Form-level errors (security, rate limiting) -->
                @error('form')
                    <flux:callout variant="danger" icon="exclamation-triangle">
                        <flux:callout.heading>Submission Error</flux:callout.heading>
                        <flux:callout.text>{{ $message }}</flux:callout.text>
                    </flux:callout>
                @enderror


                <!-- Honeypot fields (hidden from real users, bots will fill these) -->
                <div class="hidden" aria-hidden="true">
                    <input type="text" name="website" wire:model="website" tabindex="-1" autocomplete="off">
                    <input type="hidden" name="honeypot_time" wire:model="honeypot_time">
                </div>

                <!-- Personal Information -->
                <flux:fieldset>
                    <flux:legend>Your Information</flux:legend>

                    <div class="grid md:grid-cols-2 gap-6 mt-4">
                        <flux:field>
                            <flux:label badge="Required">Full Name</flux:label>
                            <flux:input wire:model="name" placeholder="Enter your full name" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Required">Email Address</flux:label>
                            <flux:input wire:model="email" type="email" placeholder="Enter your email" />
                            <flux:error name="email" />
                        </flux:field>

                        <flux:field class="md:col-span-2">
                            <flux:label>Phone Number</flux:label>
                            <flux:input wire:model="phone" placeholder="Enter your phone number" />
                            <flux:description>Optional - for urgent matters</flux:description>
                            <flux:error name="phone" />
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
                                wire:model="newAttachments"
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
                                <flux:text size="sm" class="mt-1">PDF, DOC, DOCX, JPG, PNG, GIF up to 10MB each (max 5 files)</flux:text>
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

                        @error('attachments')
                            <flux:text size="sm" class="mt-2 text-red-600">{{ $message }}</flux:text>
                        @enderror
                        @error('attachments.*')
                            <flux:text size="sm" class="mt-2 text-red-600">{{ $message }}</flux:text>
                        @enderror
                    </div>
                </flux:fieldset>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4">
                    <flux:button
                        type="button"
                        variant="primary"
                        icon="paper-airplane"
                        wire:click="validateForm"
                        wire:loading.attr="disabled"
                        wire:target="validateForm"
                    >
                        <span wire:loading.remove wire:target="validateForm">Submit Ticket</span>
                        <span wire:loading wire:target="validateForm">Validating...</span>
                    </flux:button>
                </div>
            </form>
        </flux:card>

        <!-- Security Verification Modal -->
        <flux:modal name="captcha-modal" class="max-w-md">
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
                            wire:click="submit"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                        >
                            <span wire:loading.remove wire:target="submit">Submit</span>
                            <span wire:loading wire:target="submit">Submitting...</span>
                        </flux:button>
                    </div>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
