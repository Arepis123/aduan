<?php

namespace App\Livewire\Pages;

use App\Models\Ticket;
use App\Traits\WithSecurityProtection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Check Ticket Status - Sistem Aduan CLAB')]
class CheckStatus extends Component
{
    use WithSecurityProtection;

    #[Validate('required|string')]
    public string $ticket_number = '';

    #[Validate('required|email')]
    public string $email = '';

    public ?string $error = null;

    public function mount(): void
    {
        $this->mountWithSecurityProtection();
    }

    public function search(): void
    {
        // Rate limit: 10 lookups per minute per IP
        $this->checkRateLimit('ticket-lookup', 10, 1);

        // Honeypot validation
        $this->validateHoneypot();

        $this->validate();

        $ticket = Ticket::where('ticket_number', $this->ticket_number)
            ->where('requester_email', $this->email)
            ->first();

        if ($ticket) {
            $this->redirect(route('ticket.status', $ticket->ticket_number), navigate: true);
        } else {
            $this->error = 'No ticket found with the provided ticket number and email address.';
        }
    }

    public function render()
    {
        return view('livewire.pages.check-status');
    }
}
