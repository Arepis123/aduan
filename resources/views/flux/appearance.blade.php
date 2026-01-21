@props([
    'as' => 'switch', // 'switch' or 'menu'
])

@php
$classes = Flux::classes()
    ->add('flex items-center');
@endphp

@if($as === 'switch')
    <div x-data {{ $attributes->class($classes) }}>
        <flux:switch x-model="$flux.dark" aria-label="Toggle dark mode">
            <x-slot:description>
                <span x-show="$flux.dark">Dark mode</span>
                <span x-show="!$flux.dark">Light mode</span>
            </x-slot:description>
        </flux:switch>
    </div>
@elseif($as === 'menu')
    <div x-data>
        <flux:menu.item
            :icon="$light?->attributes->get('icon', 'sun')"
            x-on:click="$flux.appearance = 'light'"
            x-bind:data-current="$flux.appearance === 'light' ? true : undefined"
        >
            {{ $light ?? 'Light' }}
        </flux:menu.item>
        <flux:menu.item
            :icon="$dark?->attributes->get('icon', 'moon')"
            x-on:click="$flux.appearance = 'dark'"
            x-bind:data-current="$flux.appearance === 'dark' ? true : undefined"
        >
            {{ $dark ?? 'Dark' }}
        </flux:menu.item>
        <flux:menu.item
            :icon="$system?->attributes->get('icon', 'computer-desktop')"
            x-on:click="$flux.appearance = 'system'"
            x-bind:data-current="$flux.appearance === 'system' ? true : undefined"
        >
            {{ $system ?? 'System' }}
        </flux:menu.item>
    </div>
@endif
