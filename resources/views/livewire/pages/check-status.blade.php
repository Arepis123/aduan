<div class="min-h-[70vh] flex flex-col items-center justify-center space-y-12">
    <flux:card class="dark:bg-zinc-900">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center mx-auto mb-4">
                <flux:icon.magnifying-glass class="size-8 text-green-600 dark:text-green-400" />
            </div>
            <flux:heading size="xl" class="mb-2">Check Ticket Status</flux:heading>
            <flux:subheading>Enter your ticket number and email to view your ticket status.</flux:subheading>
        </div>

        @if($error)
            <flux:callout variant="danger" icon="exclamation-triangle" class="mb-6">
                {{ $error }}
            </flux:callout>
        @endif

        @error('form')
            <flux:callout variant="danger" icon="exclamation-triangle" class="mb-6">
                {{ $message }}
            </flux:callout>
        @enderror

        <form wire:submit="search" class="space-y-6">
            <!-- Honeypot fields (hidden from real users) -->
            <div class="hidden" aria-hidden="true">
                <input type="text" name="website" wire:model="website" tabindex="-1" autocomplete="off">
                <input type="hidden" name="honeypot_time" wire:model="honeypot_time">
            </div>

            <flux:field>
                <flux:label badge="Required">Ticket Number</flux:label>
                <flux:input
                    wire:model="ticket_number"
                    placeholder="e.g., TKT-2026-00001"
                />
                <flux:error name="ticket_number" />
            </flux:field>

            <flux:field>
                <flux:label badge="Required">Email Address</flux:label>
                <flux:input
                    wire:model="email"
                    type="email"
                    placeholder="Enter the email used when submitting"
                />
                <flux:error name="email" />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full" icon="magnifying-glass">
                Check Status
            </flux:button>
        </form>

        <flux:separator class="my-6" />

        <flux:text class="text-center" size="sm">
            Don't have a ticket yet?
            <flux:link href="{{ route('submit') }}" wire:navigate class="font-medium">
                Submit a new ticket
            </flux:link>
        </flux:text>
    </flux:card>
</div>
