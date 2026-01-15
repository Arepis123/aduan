<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Check Ticket Status</h1>
            <p class="text-gray-600">Enter your ticket number and email to view your ticket status.</p>
        </div>

        @if($error)
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ $error }}
            </div>
        @endif

        <form wire:submit="search" class="space-y-6">
            <div>
                <flux:input
                    wire:model="ticket_number"
                    label="Ticket Number"
                    placeholder="e.g., TKT-2024-00001"
                    required
                />
            </div>

            <div>
                <flux:input
                    wire:model="email"
                    type="email"
                    label="Email Address"
                    placeholder="Enter the email used when submitting"
                    required
                />
            </div>

            <flux:button type="submit" variant="primary" class="w-full">
                <span wire:loading.remove wire:target="search">Check Status</span>
                <span wire:loading wire:target="search">Searching...</span>
            </flux:button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Don't have a ticket yet?
            <a href="{{ route('submit') }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 font-medium">
                Submit a new ticket
            </a>
        </p>
    </div>
</div>
