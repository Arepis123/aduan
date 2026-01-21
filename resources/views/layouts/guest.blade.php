<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem Aduan CLAB') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="font-sans text-zinc-900 dark:text-zinc-100 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-zinc-100 dark:bg-zinc-900">
            <div class="mb-4">
                <a href="/" wire:navigate class="flex items-center gap-2">
                    <flux:icon.document-text class="size-10 text-indigo-600" />
                    <flux:heading size="xl" class="font-semibold">{{ config('app.name') }}</flux:heading>
                </a>
            </div>

            <flux:card class="w-full sm:max-w-md">
                {{ $slot }}
            </flux:card>

            <div class="mt-6">
                <flux:appearance as="switch" />
            </div>
        </div>

        @fluxScripts
    </body>
</html>
