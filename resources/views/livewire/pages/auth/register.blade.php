<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <flux:heading size="lg" class="mb-6">Register</flux:heading>

    <form wire:submit="register" class="space-y-6">
        <flux:field>
            <flux:label badge="Required">Name</flux:label>
            <flux:input wire:model="name" placeholder="Enter your name" autofocus autocomplete="name" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label badge="Required">Email</flux:label>
            <flux:input wire:model="email" type="email" placeholder="Enter your email" autocomplete="username" />
            <flux:error name="email" />
        </flux:field>

        <flux:field>
            <flux:label badge="Required">Password</flux:label>
            <flux:input wire:model="password" type="password" placeholder="Enter your password" autocomplete="new-password" />
            <flux:error name="password" />
        </flux:field>

        <flux:field>
            <flux:label badge="Required">Confirm Password</flux:label>
            <flux:input wire:model="password_confirmation" type="password" placeholder="Confirm your password" autocomplete="new-password" />
            <flux:error name="password_confirmation" />
        </flux:field>

        <div class="flex items-center justify-between">
            <flux:link href="{{ route('login') }}" wire:navigate class="text-sm">
                Already registered?
            </flux:link>

            <flux:button type="submit" variant="primary" icon="user-plus">
                Register
            </flux:button>
        </div>
    </form>
</div>
