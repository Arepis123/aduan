<div class="min-h-[70vh] flex flex-col items-center justify-center space-y-12">
    <!-- Hero Section -->
    <div class="text-center">
        <flux:heading size="xl" class="mb-4 !text-white dark:!text-zinc-100">Welcome to Sistem Aduan CLAB</flux:heading>
        <flux:subheading size="lg" class="max-w-2xl mx-auto !text-zinc-100 dark:!text-zinc-300">
            Submit your complaints, enquiries, or feedback. We're here to help and resolve your issues promptly.
        </flux:subheading>
    </div>

    <!-- Action Cards -->
    <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
        <!-- Submit Ticket Card -->
        <flux:card class="group hover:shadow-lg transition-all cursor-pointer transition-transform duration-300 ease-out hover:scale-103 hover:shadow-xl !bg-white/35 dark:!bg-zinc-800/80" href="{{ route('submit') }}" wire:navigate>
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/50 rounded-full flex items-center justify-center group-hover:bg-indigo-200 dark:group-hover:bg-indigo-900 transition-colors">
                    <flux:icon.plus-circle class="size-7 text-indigo-600 dark:text-indigo-400" />
                </div>
                <flux:heading size="lg">Submit a Ticket</flux:heading>
            </div>
            <flux:text class="text-zinc-900 dark:text-zinc-400">
                Have an issue or enquiry? Submit a new ticket and our team will get back to you as soon as possible.
            </flux:text>
        </flux:card>

        <!-- Check Status Card -->
        <flux:card class="group hover:shadow-lg transition-all cursor-pointer transition-transform duration-300 ease-out hover:scale-103 hover:shadow-xl !bg-white/35 dark:!bg-zinc-800/80" href="{{ route('check') }}" wire:navigate>
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center group-hover:bg-green-200 dark:group-hover:bg-green-900 transition-colors">
                    <flux:icon.magnifying-glass class="size-7 text-green-600 dark:text-green-400" />
                </div>
                <flux:heading size="lg">Check Ticket Status</flux:heading>
            </div>
            <flux:text class="text-zinc-900 dark:text-zinc-400">
                Already submitted a ticket? Check the current status and view responses using your ticket number.
            </flux:text>
        </flux:card>
    </div>
</div>
