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
        <div class="min-h-screen flex flex-col sm:justify-center sm:items-end items-center pt-6 sm:pt-0 sm:pr-16 relative">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('images/background-2.jpg') }}');"></div>


            <!-- <div class="relative z-10 mb-4">
                <a href="/" wire:navigate class="flex items-center gap-2">
                    <flux:icon.document-text class="size-10 text-indigo-600" />
                    <flux:heading size="xl" class="font-semibold">{{ config('app.name') }}</flux:heading>
                </a>
            </div> -->

            <flux:card class="relative z-10 w-full sm:max-w-sm !bg-white dark:!bg-zinc-800 !bg-opacity-95 dark:!bg-opacity-95">
                {{ $slot }}
                <div class="relative z-10 mt-6">
                    <flux:appearance as="switch" />
                </div>
            </flux:card>
        </div>

        @fluxScripts
    </body>
</html>
