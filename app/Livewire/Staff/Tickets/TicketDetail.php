<?php

namespace App\Livewire\Staff\Tickets;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class TicketDetail extends Component
{
    public Ticket $ticket;

    #[Validate('required|string|min:5')]
    public string $reply = '';

    public bool $isInternalNote = false;

    public ?int $assignTo = null;
    public string $newStatus = '';
    public string $newPriority = '';

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket->load(['department', 'category', 'assignedAgent', 'replies.user', 'attachments']);
        $this->assignTo = $ticket->user_id;
        $this->newStatus = $ticket->status;
        $this->newPriority = $ticket->priority;
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
            'user_id' => Auth::id(),
            'message' => $this->reply,
            'is_client_reply' => false,
            'is_internal_note' => $this->isInternalNote,
        ]);

        $this->reply = '';
        $this->isInternalNote = false;
        $this->ticket->refresh();
        $this->ticket->load('replies.user');
    }

    public function updateAssignment(): void
    {
        $this->ticket->update(['user_id' => $this->assignTo ?: null]);
        $this->ticket->refresh();
    }

    public function updateStatus(): void
    {
        $data = ['status' => $this->newStatus];

        if ($this->newStatus === 'resolved') {
            $data['resolved_at'] = now();
        } elseif ($this->newStatus === 'closed') {
            $data['closed_at'] = now();
        }

        $this->ticket->update($data);
        $this->ticket->refresh();
    }

    public function updatePriority(): void
    {
        $this->ticket->update(['priority' => $this->newPriority]);
        $this->ticket->refresh();
    }

    public function assignToMe(): void
    {
        $this->assignTo = Auth::id();
        $this->updateAssignment();
    }

    public function render()
    {
        return view('livewire.staff.tickets.ticket-detail', [
            'agents' => User::whereIn('role', ['admin', 'agent'])->get(),
        ]);
    }
}
