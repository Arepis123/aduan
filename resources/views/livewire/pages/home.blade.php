<div>
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Welcome to Sistem Aduan</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Submit your complaints, enquiries, or feedback. We're here to help and resolve your issues promptly.
        </p>
    </div>

    <!-- Action Cards -->
    <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto mb-12">
        <!-- Submit Ticket Card -->
        <a href="{{ route('submit') }}" wire:navigate
           class="group bg-white rounded-xl shadow-sm border border-gray-200 p-8 hover:shadow-lg hover:border-indigo-300 transition-all">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <h2 class="text-2xl font-semibold text-gray-900">Submit a Ticket</h2>
            </div>
            <p class="text-gray-600">
                Have an issue or enquiry? Submit a new ticket and our team will get back to you as soon as possible.
            </p>
        </a>

        <!-- Check Status Card -->
        <a href="{{ route('check') }}" wire:navigate
           class="group bg-white rounded-xl shadow-sm border border-gray-200 p-8 hover:shadow-lg hover:border-green-300 transition-all">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-semibold text-gray-900">Check Ticket Status</h2>
            </div>
            <p class="text-gray-600">
                Already submitted a ticket? Check the current status and view responses using your ticket number.
            </p>
        </a>
    </div>

    <!-- Stats Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 max-w-4xl mx-auto">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 text-center">Current Status</h3>
        <div class="grid grid-cols-2 gap-8">
            <div class="text-center">
                <div class="text-4xl font-bold text-indigo-600 mb-2">{{ $openTickets }}</div>
                <div class="text-gray-600">Open Tickets</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-green-600 mb-2">{{ $resolvedTickets }}</div>
                <div class="text-gray-600">Resolved This Month</div>
            </div>
        </div>
    </div>
</div>
