<div class="space-y-6">
    <div>
        <flux:heading size="xl">Settings</flux:heading>
        <flux:subheading>Manage your account settings and preferences</flux:subheading>
    </div>

    <div class="grid gap-6 max-w-2xl">
        <!-- Appearance Settings -->
        <flux:card>
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">Appearance</flux:heading>
                    <flux:subheading>Customize how the system looks on your device</flux:subheading>
                </div>

                <flux:separator />

                <div class="flex items-center justify-between">
                    <div>
                        <flux:text class="font-medium">Theme</flux:text>
                        <flux:text size="sm" class="text-zinc-500">Select your preferred color scheme</flux:text>
                    </div>                  
                </div>
                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                </flux:radio.group>  
            </div>
        </flux:card>

        <!-- Password Settings -->
        <flux:card>
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">Change Password</flux:heading>
                    <flux:subheading>Ensure your account is using a secure password</flux:subheading>
                </div>

                <flux:separator />

                <form wire:submit="updatePassword" class="space-y-4">
                    <flux:field>
                        <flux:label badge="Required">Current Password</flux:label>
                        <flux:input wire:model="current_password" type="password" placeholder="Enter current password" autocomplete="current-password" />
                        <flux:error name="current_password" />
                    </flux:field>

                    <flux:field>
                        <flux:label badge="Required">New Password</flux:label>
                        <flux:input wire:model="password" type="password" placeholder="Enter new password" autocomplete="new-password" />
                        <flux:error name="password" />
                    </flux:field>

                    <flux:field>
                        <flux:label badge="Required">Confirm New Password</flux:label>
                        <flux:input wire:model="password_confirmation" type="password" placeholder="Confirm new password" autocomplete="new-password" />
                        <flux:error name="password_confirmation" />
                    </flux:field>

                    <div class="flex items-center gap-4 pt-2">
                        <flux:button type="submit" variant="primary">Update Password</flux:button>

                        <flux:text
                            x-data="{ shown: false }"
                            x-init="@this.on('password-updated', () => { shown = true; setTimeout(() => shown = false, 3000); })"
                            x-show="shown"
                            x-transition
                            class="text-green-600 dark:text-green-400"
                        >
                            Password updated successfully!
                        </flux:text>
                    </div>
                </form>
            </div>
        </flux:card>

        <!-- Account Information -->
        <flux:card>
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">Account Information</flux:heading>
                    <flux:subheading>Your account details</flux:subheading>
                </div>

                <flux:separator />

                <div class="grid gap-4">
                    <div class="flex justify-between items-center">
                        <flux:text class="text-zinc-500">Name</flux:text>
                        <flux:text class="font-medium">{{ auth()->user()->name }}</flux:text>
                    </div>
                    <div class="flex justify-between items-center">
                        <flux:text class="text-zinc-500">Email</flux:text>
                        <flux:text class="font-medium">{{ auth()->user()->email }}</flux:text>
                    </div>
                    <div class="flex justify-between items-center">
                        <flux:text class="text-zinc-500">Role</flux:text>
                        <flux:badge color="{{ auth()->user()->role === 'admin' ? 'purple' : 'blue' }}">
                            {{ ucfirst(auth()->user()->role) }}
                        </flux:badge>
                    </div>
                    @if(auth()->user()->department)
                    <div class="flex justify-between items-center">
                        <flux:text class="text-zinc-500">Department</flux:text>
                        <flux:text class="font-medium">{{ auth()->user()->department->name }}</flux:text>
                    </div>
                    @endif
                </div>
            </div>
        </flux:card>
    </div>
</div>
