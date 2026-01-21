<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <flux:heading size="lg" class="mb-2">Profile Information</flux:heading>
    <flux:subheading class="mb-6">Update your account's profile information and email address.</flux:subheading>

    <form wire:submit="updateProfileInformation" class="space-y-6">
        <flux:field>
            <flux:label badge="Required">Name</flux:label>
            <flux:input wire:model="name" placeholder="Enter your name" autofocus autocomplete="name" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label badge="Required">Email</flux:label>
            <flux:input wire:model="email" type="email" placeholder="Enter your email" autocomplete="username" />
            <flux:error name="email" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-2">
                    <flux:text size="sm" class="text-amber-600 dark:text-amber-400">
                        Your email address is unverified.
                        <flux:link wire:click.prevent="sendVerification" class="cursor-pointer">
                            Click here to re-send the verification email.
                        </flux:link>
                    </flux:text>

                    @if (session('status') === 'verification-link-sent')
                        <flux:text size="sm" class="mt-2 text-green-600 dark:text-green-400">
                            A new verification link has been sent to your email address.
                        </flux:text>
                    @endif
                </div>
            @endif
        </flux:field>

        <div class="flex items-center gap-4">
            <flux:button type="submit" variant="primary">Save</flux:button>

            <flux:text x-data="{ shown: false }" x-init="@this.on('profile-updated', () => { shown = true; setTimeout(() => shown = false, 2000); })" x-show="shown" x-transition class="text-green-600 dark:text-green-400">
                Saved.
            </flux:text>
        </div>
    </form>
</section>
