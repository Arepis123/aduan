<?php

namespace App\Livewire\Pages;

use App\Models\Ticket;
use App\Models\TicketReply;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.public')]
class TicketStatus extends Component
{
    public Ticket $ticket;

    #[Validate('required|string|min:10')]
    public string $reply = '';

    public bool $showReplyForm = false;

    public function mount(string $ticketNumber): void
    {
        $this->ticket = Ticket::where('ticket_number', $ticketNumber)
            ->with(['department', 'category', 'assignedAgent', 'publicReplies.user', 'attachments'])
            ->firstOrFail();
    }

    public function getTitle(): string
    {
        return $this->ticket->ticket_number . ' - Sistem Aduan';
    }

    public function submitReply(): void
    {
        $this->validate();

        TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => null,
            'message' => $this->reply,
            'is_client_reply' => true,
            'is_internal_note' => false,
        ]);

        // Reopen ticket if it was resolved/closed
        if (in_array($this->ticket->status, ['resolved', 'closed'])) {
            $this->ticket->update(['status' => 'open']);
        }

        $this->reply = '';
        $this->showReplyForm = false;
        $this->ticket->refresh();
        $this->ticket->load('publicReplies.user');
    }

    public function render()
    {
        return view('livewire.pages.ticket-status');
    }
}
