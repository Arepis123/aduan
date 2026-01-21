<?php

namespace App\Livewire\Staff\Tickets;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\Unit;
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

    public ?int $assignDepartment = null;
    public ?int $assignUnit = null;
    public string $newStatus = '';
    public string $newPriority = '';

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket->load(['department', 'unit', 'category', 'replies.user', 'attachments']);
        $this->assignDepartment = $ticket->department_id;
        $this->assignUnit = $ticket->unit_id;
        $this->newStatus = $ticket->status;
        $this->newPriority = $ticket->priority;
    }

    public function getTitle(): string
    {
        return $this->ticket->ticket_number . ' - Sistem Aduan CLAB';
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
        $this->ticket->update([
            'department_id' => $this->assignDepartment ?: null,
            'unit_id' => $this->assignUnit ?: null,
        ]);
        $this->ticket->refresh();
        $this->ticket->load(['department', 'unit']);
    }

    public function updatedAssignDepartment($value): void
    {
        // Reset unit when department changes
        $this->assignUnit = null;
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

    public function render()
    {
        $departments = Department::active()->orderBy('name')->get();
        $units = $this->assignDepartment
            ? Unit::where('department_id', $this->assignDepartment)->active()->orderBy('name')->get()
            : collect();

        return view('livewire.staff.tickets.ticket-detail', [
            'departments' => $departments,
            'units' => $units,
        ]);
    }
}
