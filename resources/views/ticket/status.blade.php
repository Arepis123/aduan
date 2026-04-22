<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket Status – {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="font-sans antialiased bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-lg">
        <div class="mb-6 text-center">
            <flux:heading size="xl" class="font-semibold">{{ config('app.name') }}</flux:heading>
            <flux:subheading>Ticket Status</flux:subheading>
        </div>

        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ $ticket->ticket_number }}</flux:heading>
                <flux:badge color="{{ $ticket->status_color }}">
                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                </flux:badge>
            </div>

            <flux:separator />

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-zinc-500 dark:text-zinc-400">Subject</span>
                    <span class="font-medium text-right max-w-xs">{{ $ticket->subject }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-zinc-500 dark:text-zinc-400">Priority</span>
                    <flux:badge color="{{ $ticket->priority_color }}" size="sm">
                        {{ ucfirst($ticket->priority) }}
                    </flux:badge>
                </div>

                @if ($ticket->category)
                <div class="flex justify-between">
                    <span class="text-zinc-500 dark:text-zinc-400">Category</span>
                    <span class="font-medium">{{ $ticket->category->name }}</span>
                </div>
                @endif

                <div class="flex justify-between">
                    <span class="text-zinc-500 dark:text-zinc-400">Submitted</span>
                    <span class="font-medium">{{ $ticket->created_at->format('d M Y, h:i A') }}</span>
                </div>

                @if ($ticket->assigned_at)
                <div class="flex justify-between">
                    <span class="text-zinc-500 dark:text-zinc-400">Assigned</span>
                    <span class="font-medium">{{ $ticket->assigned_at->format('d M Y, h:i A') }}</span>
                </div>
                @endif

                @if ($ticket->resolved_at)
                <div class="flex justify-between">
                    <span class="text-zinc-500 dark:text-zinc-400">Resolved</span>
                    <span class="font-medium">{{ $ticket->resolved_at->format('d M Y, h:i A') }}</span>
                </div>
                @endif

                @if ($ticket->closed_at)
                <div class="flex justify-between">
                    <span class="text-zinc-500 dark:text-zinc-400">Closed</span>
                    <span class="font-medium">{{ $ticket->closed_at->format('d M Y, h:i A') }}</span>
                </div>
                @endif
            </div>

            @if ($ticket->closing_remark)
            <flux:separator />
            <div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">Closing Remark</p>
                <p class="text-sm">{{ $ticket->closing_remark }}</p>
            </div>
            @endif
        </flux:card>

        <p class="text-center text-xs text-zinc-500 dark:text-zinc-400 mt-4">
            If you have further questions, please contact support and reference your ticket number.
        </p>
    </div>

    @fluxScripts
</body>
</html>
