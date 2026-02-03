<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem Aduan CLAB') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/favicon-96x96.png') }}" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}" />
        <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}" />
        <meta name="apple-mobile-web-app-title" content="Aduan" />
        <link rel="manifest" href="{{ asset('images/site.webmanifest') }}" />        

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible class="border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <flux:sidebar.header>
                <flux:sidebar.brand
                    href="{{ route('staff.dashboard') }}"
                    logo="{{ asset('images/logo-clab.png') }}"
                    logo:dark="{{ asset('images/logo-clab.png') }}"
                    name="{{ config('app.name') }}"
                />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item href="{{ route('staff.dashboard') }}" :current="request()->routeIs('staff.dashboard')" wire:navigate icon="home">
                    Dashboard
                </flux:sidebar.item>
                <flux:sidebar.item href="{{ route('staff.submit') }}" :current="request()->routeIs('staff.submit')" wire:navigate icon="plus-circle">
                    Submit Ticket
                </flux:sidebar.item>
                <flux:sidebar.item href="{{ route('staff.tickets.index') }}" :current="request()->routeIs('staff.tickets.*')" wire:navigate icon="ticket">
                    Tickets
                </flux:sidebar.item>
            </flux:sidebar.nav>          

            @can('users.view')
            <div class="px-3 mt-4 in-data-flux-sidebar-collapsed-desktop:hidden">
                <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-400 uppercase tracking-wider">{{ __('ADMINISTRATOR') }}</h3>
            </div>              
            <flux:sidebar.nav>
                <flux:sidebar.item href="{{ route('staff.users.index') }}" :current="request()->routeIs('staff.users.*')" wire:navigate icon="users">
                    Users
                </flux:sidebar.item>
                <flux:sidebar.item href="{{ route('staff.sectors.index') }}" :current="request()->routeIs('staff.sectors.*')" wire:navigate icon="building-library">
                    Sectors
                </flux:sidebar.item>
                <flux:sidebar.item href="{{ route('staff.departments.index') }}" :current="request()->routeIs('staff.departments.*')" wire:navigate icon="building-office">
                    Departments
                </flux:sidebar.item>
                <flux:sidebar.item href="{{ route('staff.units.index') }}" :current="request()->routeIs('staff.units.*')" wire:navigate icon="square-3-stack-3d">
                    Units
                </flux:sidebar.item>
                <flux:sidebar.item href="{{ route('staff.categories.index') }}" :current="request()->routeIs('staff.categories.*')" wire:navigate icon="tag">
                    Categories
                </flux:sidebar.item>
            </flux:sidebar.nav>
            @endcan

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item href="{{ route('staff.settings') }}" wire:navigate icon="cog-6-tooth">
                    Settings
                </flux:sidebar.item>
                <flux:sidebar.item href="{{ route('home') }}" wire:navigate icon="globe-alt">
                    Public Site
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <!-- User Menu -->
            <flux:dropdown position="top" align="start" class="max-lg:hidden">
                <flux:sidebar.profile
                    :name="auth()->user()->name"
                    :initials="substr(auth()->user()->name, 0, 2)"
                    icon-trailing="chevron-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <flux:avatar size="sm" :initials="substr(auth()->user()->name, 0, 2)" />
                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-zinc-500">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item type="submit" icon="arrow-right-start-on-rectangle">
                            Log Out
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header container class="lg:hidden! border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="bottom" align="end">
                <flux:profile
                    :initials="substr(auth()->user()->name, 0, 2)"
                />

                <flux:menu>
                    <flux:menu.item href="{{ route('profile') }}" icon="user" wire:navigate>Profile</flux:menu.item>
                    <flux:menu.separator />
                    <flux:appearance as="menu">
                        <x-slot:light icon="sun">Light</x-slot:light>
                        <x-slot:dark icon="moon">Dark</x-slot:dark>
                        <x-slot:system icon="computer-desktop">System</x-slot:system>
                    </flux:appearance>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item type="submit" icon="arrow-right-start-on-rectangle">
                            Log Out
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <flux:main>
            {{ $slot }}
        </flux:main>

        @fluxScripts
    </body>
</html>
