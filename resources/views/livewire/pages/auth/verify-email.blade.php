<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <flux:heading size="lg" class="mb-4">Verify Email</flux:heading>

    <flux:text class="mb-6">
        Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
    </flux:text>

    @if (session('status') == 'verification-link-sent')
        <flux:callout variant="success" class="mb-6">
            A new verification link has been sent to the email address you provided during registration.
        </flux:callout>
    @endif

    <div class="flex items-center justify-between">
        <flux:button wire:click="sendVerification" variant="primary" icon="envelope">
            Resend Verification Email
        </flux:button>

        <flux:button wire:click="logout" variant="ghost">
            Log Out
        </flux:button>
    </div>
</div>
