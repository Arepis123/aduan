<div class="max-w-3xl mx-auto">
    @if($submittedTicket)
        <!-- Success Message -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Ticket Submitted Successfully!</h2>
            <p class="text-gray-600 mb-6">Your ticket has been received. Please save your ticket number for reference.</p>

            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <p class="text-sm text-gray-500 mb-2">Your Ticket Number</p>
                <p class="text-3xl font-bold text-indigo-600">{{ $submittedTicket->ticket_number }}</p>
            </div>

            <p class="text-sm text-gray-500 mb-6">
                A confirmation email has been sent to <strong>{{ $submittedTicket->requester_email }}</strong>
            </p>

            <div class="flex justify-center gap-4">
                <a href="{{ route('ticket.status', $submittedTicket->ticket_number) }}" wire:navigate
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700">
                    View Ticket Status
                </a>
                <a href="{{ route('submit') }}" wire:navigate
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200">
                    Submit Another Ticket
                </a>
            </div>
        </div>
    @else
        <!-- Ticket Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Submit a New Ticket</h1>
            <p class="text-gray-600 mb-8">Fill out the form below to submit your complaint or enquiry.</p>

            <form wire:submit="submit" class="space-y-6">
                <!-- Personal Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Your Information</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <flux:input
                                wire:model="name"
                                label="Full Name"
                                placeholder="Enter your full name"
                                required
                            />
                        </div>
                        <div>
                            <flux:input
                                wire:model="email"
                                type="email"
                                label="Email Address"
                                placeholder="Enter your email"
                                required
                            />
                        </div>
                        <div class="md:col-span-2">
                            <flux:input
                                wire:model="phone"
                                label="Phone Number (Optional)"
                                placeholder="Enter your phone number"
                            />
                        </div>
                    </div>
                </div>

                <!-- Ticket Details -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ticket Details</h3>
                    <div class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <flux:select
                                    wire:model.live="department_id"
                                    label="Department"
                                    placeholder="Select a department"
                                    required
                                >
                                    <flux:select.option value="">Select a department</flux:select.option>
                                    @foreach($departments as $department)
                                        <flux:select.option value="{{ $department->id }}">
                                            {{ $department->name }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>
                            <div>
                                <flux:select
                                    wire:model="category_id"
                                    label="Category"
                                    placeholder="Select a category"
                                    required
                                    :disabled="empty($categories)"
                                >
                                    <flux:select.option value="">Select a category</flux:select.option>
                                    @foreach($categories as $category)
                                        <flux:select.option value="{{ $category['id'] }}">
                                            {{ $category['name'] }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>
                        </div>

                        <div>
                            <flux:input
                                wire:model="subject"
                                label="Subject"
                                placeholder="Brief description of your issue"
                                required
                            />
                        </div>

                        <div>
                            <flux:textarea
                                wire:model="description"
                                label="Description"
                                placeholder="Please provide detailed information about your issue or enquiry (minimum 20 characters)"
                                rows="5"
                                required
                            />
                        </div>

                        <div>
                            <flux:select
                                wire:model="priority"
                                label="Priority"
                            >
                                <flux:select.option value="low">Low - General enquiry</flux:select.option>
                                <flux:select.option value="medium">Medium - Standard issue</flux:select.option>
                                <flux:select.option value="high">High - Urgent matter</flux:select.option>
                                <flux:select.option value="urgent">Urgent - Critical issue</flux:select.option>
                            </flux:select>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="pb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Attachments (Optional)</h3>
                    <div
                        x-data="{ dragging: false }"
                        x-on:dragover.prevent="dragging = true"
                        x-on:dragleave.prevent="dragging = false"
                        x-on:drop.prevent="dragging = false"
                        class="border-2 border-dashed rounded-lg p-6 text-center transition-colors"
                        :class="dragging ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300'"
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
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">
                                <span class="font-medium text-indigo-600 hover:text-indigo-500">Click to upload</span>
                                or drag and drop
                            </p>
                            <p class="mt-1 text-xs text-gray-500">PDF, DOC, DOCX, JPG, PNG, GIF up to 10MB each</p>
                        </label>
                    </div>

                    @if(count($attachments) > 0)
                        <ul class="mt-4 space-y-2">
                            @foreach($attachments as $index => $attachment)
                                <li class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-2">
                                    <span class="text-sm text-gray-700 truncate">{{ $attachment->getClientOriginalName() }}</span>
                                    <button
                                        type="button"
                                        wire:click="removeAttachment({{ $index }})"
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @error('attachments.*')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary" class="px-8">
                        <span wire:loading.remove wire:target="submit">Submit Ticket</span>
                        <span wire:loading wire:target="submit">Submitting...</span>
                    </flux:button>
                </div>
            </form>
        </div>
    @endif
</div>
