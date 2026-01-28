<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('staff.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    @if (session('status'))
        <flux:callout variant="success" class="mb-4">
            {{ session('status') }}
        </flux:callout>
    @endif

    <flux:heading size="lg" class="mb-6">Staff Login</flux:heading>

    <form wire:submit="login" class="space-y-6">
        <!-- Email Address -->
        <flux:field>
            <flux:label>Email</flux:label>
            <flux:input wire:model="form.email" type="email" placeholder="Enter your email" autofocus autocomplete="username" />
            <flux:error name="form.email" />
        </flux:field>

        <!-- Password -->
        <flux:field>
            <flux:label>Password</flux:label>
            <flux:input wire:model="form.password" type="password" placeholder="Enter your password" autocomplete="current-password" />
            <flux:error name="form.password" />
        </flux:field>

        <!-- Remember Me -->
        <!-- <flux:checkbox wire:model="form.remember" label="Remember me" /> -->

        <div class="flex items-center justify-between">
            @if (Route::has('password.request'))
                <flux:link href="{{ route('password.request') }}" wire:navigate class="text-sm">
                    Forgot your password?
                </flux:link>
            @else
                <div></div>
            @endif

            <flux:button type="submit" variant="primary" icon="arrow-right-end-on-rectangle">
                Log in
            </flux:button>
        </div>
    </form>

    <!-- <flux:separator class="my-6" />

    <flux:text class="text-center" size="sm">
        <flux:link href="{{ route('home') }}" wire:navigate>
            &larr; Back to public site
        </flux:link>
    </flux:text> -->
</div>
