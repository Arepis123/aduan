<div class="max-w-4xl mx-auto">
    @if($submittedTicket)
        <!-- Success Message -->
        <flux:card class="text-center">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center mx-auto mb-4">
                <flux:icon.check class="size-8 text-green-600 dark:text-green-400" />
            </div>
            <flux:heading size="xl" class="mb-2">Ticket Submitted Successfully!</flux:heading>
            <flux:text class="mb-6">The complaint/enquiry has been logged in the system.</flux:text>

            <div class="bg-zinc-100 dark:bg-zinc-800 rounded-lg p-6 mb-6">
                <flux:text size="sm" class="mb-2">Ticket Number</flux:text>
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
            <flux:heading size="xl" class="mb-2">Submit Ticket</flux:heading>
            <flux:subheading class="mb-8">Log a complaint or enquiry received via email or WhatsApp.</flux:subheading>

            <form wire:submit="submit" class="space-y-6">
                <!-- Complainant Information -->
                <flux:fieldset>
                    <flux:legend>Complainant Information</flux:legend>

                    <div class="space-y-4 mt-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label badge="Required">Complainant Name</flux:label>
                                <flux:input wire:model="complainant_name" placeholder="Full name of complainant" />
                                <flux:error name="complainant_name" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Phone Number</flux:label>
                                <flux:input wire:model="complainant_phone" placeholder="e.g. 01X-XXXXXXX" />
                                <flux:error name="complainant_phone" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Email Address</flux:label>
                            <flux:input wire:model="complainant_email" type="email" placeholder="e.g. example@email.com" />
                            <flux:error name="complainant_email" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Company / Organisation</flux:label>
                            <flux:input wire:model="complainant_company" placeholder="Company or organisation name" />
                            <flux:error name="complainant_company" />
                        </flux:field>
                    </div>
                </flux:fieldset>

                <flux:separator />

                <!-- Ticket Details -->
                <flux:fieldset>
                    <flux:legend>Ticket Details</flux:legend>

                    <div class="space-y-4 mt-4">
                        <flux:field>
                            <flux:label>Category</flux:label>
                            <flux:select variant="listbox" wire:model="category_id" placeholder="Select a category">
                                <flux:select.option value="">-- No Category --</flux:select.option>
                                @foreach($categories as $category)
                                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="category_id" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Required">Subject</flux:label>
                            <flux:input wire:model="subject" placeholder="Brief description of the issue" />
                            <flux:error name="subject" />
                        </flux:field>

                        <flux:field>
                            <flux:label badge="Required">Description</flux:label>
                            <flux:editor
                                wire:model="description"
                                placeholder="Provide detailed information about the complaint or enquiry"
                                toolbar="heading | bold italic underline strike | bullet ordered blockquote | link | undo redo"
                                class="[&_[data-slot=content]]:min-h-[160px]!"
                            />
                            <flux:description>Minimum 20 characters</flux:description>
                            <flux:error name="description" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Priority</flux:label>
                            <flux:select variant="listbox" wire:model="priority">
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
