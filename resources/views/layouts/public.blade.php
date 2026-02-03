<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Sistem Aduan CLAB') }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600|fredoka:400|albert-sans:600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800 antialiased flex flex-col">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:brand href="{{ route('home') }}" logo="{{ asset('images/logo-clab.png') }}" name="Sistem Aduan CLAB" class="max-lg:hidden dark:hidden" />
            <flux:brand href="{{ route('home') }}" logo="{{ asset('images/logo-clab.png') }}" name="Sistem Aduan CLAB" class="max-lg:hidden! hidden dark:flex" />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="home" href="{{ route('home') }}" :current="request()->routeIs('home')" wire:navigate>
                    Home
                </flux:navbar.item>
                <flux:navbar.item icon="ticket" href="{{ route('submit') }}" :current="request()->routeIs('submit')" wire:navigate>
                    Submit Ticket
                </flux:navbar.item>
                <flux:navbar.item icon="magnifying-glass" href="{{ route('check') }}" :current="request()->routeIs('check')" wire:navigate>
                    Check Status
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-4">
                <!-- Dark Mode Toggle Button -->
                <div x-data class="flex items-center">
                    <flux:button variant="ghost" size="sm" square x-on:click="$flux.dark = !$flux.dark" aria-label="Toggle dark mode">
                        <!-- Sun icon (shown in dark mode) -->
                        <svg x-show="$flux.dark" xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 2a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 2ZM10 15a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 15ZM10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM15.657 5.404a.75.75 0 1 0-1.06-1.06l-1.061 1.06a.75.75 0 0 0 1.06 1.061l1.06-1.06ZM6.464 14.596a.75.75 0 1 0-1.06-1.06l-1.06 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM18 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 18 10ZM5 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 5 10ZM14.596 15.657a.75.75 0 0 0 1.06-1.06l-1.06-1.061a.75.75 0 1 0-1.06 1.06l1.06 1.06ZM5.404 6.464a.75.75 0 0 0 1.06-1.06l-1.06-1.06a.75.75 0 1 0-1.061 1.06l1.06 1.06Z"/>
                        </svg>
                        <!-- Moon icon (shown in light mode) -->
                        <svg x-show="!$flux.dark" xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.455 2.004a.75.75 0 0 1 .26.77 7 7 0 0 0 9.958 7.967.75.75 0 0 1 1.067.853A8.5 8.5 0 1 1 6.647 1.921a.75.75 0 0 1 .808.083Z" clip-rule="evenodd"/>
                        </svg>
                    </flux:button>
                </div>
            </flux:navbar>

            <flux:separator vertical class="my-3 me-4"/>

            @auth
                <flux:button href="{{ route('staff.dashboard') }}" variant="primary" size="sm" wire:navigate>
                    Go to Dashboard
                </flux:button>
            @else
                <flux:button href="{{ route('login') }}" variant="primary" size="sm" wire:navigate>
                    Staff Login
                </flux:button>
            @endauth
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar stashable sticky class="lg:hidden border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <flux:navlist variant="outline">
                <flux:navlist.item href="{{ route('home') }}" :current="request()->routeIs('home')" wire:navigate icon="home">
                    Home
                </flux:navlist.item>
                <flux:navlist.item href="{{ route('submit') }}" :current="request()->routeIs('submit')" wire:navigate icon="ticket">
                    Submit Ticket
                </flux:navlist.item>
                <flux:navlist.item href="{{ route('check') }}" :current="request()->routeIs('check')" wire:navigate icon="magnifying-glass">
                    Check Status
                </flux:navlist.item>
            </flux:navlist>

            <flux:spacer />

            <!-- Dark Mode Toggle for Mobile -->
            <div x-data class="px-3 py-2 flex items-center justify-between">
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Dark Mode</span>
                <flux:button variant="ghost" size="sm" square x-on:click="$flux.dark = !$flux.dark" aria-label="Toggle dark mode">
                    <!-- Sun icon (shown in dark mode) -->
                    <svg x-show="$flux.dark" xmlns="http://www.w3.org/2000/svg" class="size-5 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 2a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 2ZM10 15a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 15ZM10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM15.657 5.404a.75.75 0 1 0-1.06-1.06l-1.061 1.06a.75.75 0 0 0 1.06 1.061l1.06-1.06ZM6.464 14.596a.75.75 0 1 0-1.06-1.06l-1.06 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM18 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 18 10ZM5 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 5 10ZM14.596 15.657a.75.75 0 0 0 1.06-1.06l-1.06-1.061a.75.75 0 1 0-1.06 1.06l1.06 1.06ZM5.404 6.464a.75.75 0 0 0 1.06-1.06l-1.06-1.06a.75.75 0 1 0-1.061 1.06l1.06 1.06Z"/>
                    </svg>
                    <!-- Moon icon (shown in light mode) -->
                    <svg x-show="!$flux.dark" xmlns="http://www.w3.org/2000/svg" class="size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.455 2.004a.75.75 0 0 1 .26.77 7 7 0 0 0 9.958 7.967.75.75 0 0 1 1.067.853A8.5 8.5 0 1 1 6.647 1.921a.75.75 0 0 1 .808.083Z" clip-rule="evenodd"/>
                    </svg>
                </flux:button>
            </div>

            <flux:navlist variant="outline">
                @auth
                    <flux:navlist.item href="{{ route('staff.dashboard') }}" wire:navigate icon="rectangle-group">
                        Go to Dashboard
                    </flux:navlist.item>
                @else
                    <flux:navlist.item href="{{ route('login') }}" wire:navigate icon="arrow-right-end-on-rectangle">
                        Staff Login
                    </flux:navlist.item>
                @endauth
            </flux:navlist>
        </flux:sidebar>

        <!-- Page Content -->
        <main class="flex-1 relative">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('images/background-3.jpg') }}');"></div>
            <div class="absolute inset-0 dark:bg-black/40"></div>
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {{ $slot }}
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <flux:text class="text-center text-sm">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </flux:text>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
