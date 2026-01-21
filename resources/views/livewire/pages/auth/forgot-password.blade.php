<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <flux:heading size="lg" class="mb-4">Forgot Password</flux:heading>

    <flux:text class="mb-6">
        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
    </flux:text>

    <!-- Session Status -->
    @if (session('status'))
        <flux:callout variant="success" class="mb-4">
            {{ session('status') }}
        </flux:callout>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-6">
        <flux:field>
            <flux:label badge="Required">Email</flux:label>
            <flux:input wire:model="email" type="email" placeholder="Enter your email" autofocus />
            <flux:error name="email" />
        </flux:field>

        <div class="flex items-center justify-between">
            <flux:link href="{{ route('login') }}" wire:navigate class="text-sm">
                &larr; Back to login
            </flux:link>

            <flux:button type="submit" variant="primary" icon="envelope">
                Send Reset Link
            </flux:button>
        </div>
    </form>
</div>
