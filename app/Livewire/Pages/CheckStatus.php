<?php

namespace App\Livewire\Pages;

use App\Models\Ticket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Check Ticket Status - Sistem Aduan')]
class CheckStatus extends Component
{
    #[Validate('required|string')]
    public string $ticket_number = '';

    #[Validate('required|email')]
    public string $email = '';

    public ?string $error = null;

    public function search(): void
    {
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
