<?php

namespace App\Livewire\Staff\Tickets;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\Unit;
use App\Notifications\TicketAssigned;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TicketDetail extends Component
{
    public Ticket $ticket;

    public ?int $assignDepartment = null;
    public ?int $assignUnit = null;
    public string $newStatus = '';
    public string $newPriority = '';

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket->load(['department', 'unit', 'category', 'attachments']);
        $this->assignDepartment = $ticket->department_id;
        $this->assignUnit = $ticket->unit_id;
        $this->newStatus = $ticket->status;
        $this->newPriority = $ticket->priority;
    }

    public function getTitle(): string
    {
        return $this->ticket->ticket_number . ' - Sistem Aduan CLAB';
    }

    public function updateAssignment(): void
    {
        $isNewAssignment = !$this->ticket->assigned_at && ($this->assignDepartment || $this->assignUnit);

        $data = [
            'department_id' => $this->assignDepartment ?: null,
            'unit_id' => $this->assignUnit ?: null,
        ];

        // Set assigned_at on first assignment
        if ($isNewAssignment) {
            $data['assigned_at'] = now();
            $data['status'] = 'in_progress';
            $this->newStatus = 'in_progress';
        }

        $this->ticket->update($data);
        $this->ticket->refresh();
        $this->ticket->load(['department', 'unit']);

        // Send email notification
        if ($this->assignDepartment || $this->assignUnit) {
            $this->sendAssignmentNotification();
        }
    }

    protected function sendAssignmentNotification(): void
    {
        $emails = collect();

        // Get emails from unit if assigned
        if ($this->ticket->unit_id && $this->ticket->unit?->emails) {
            $emails = $emails->merge($this->ticket->unit->emails);
        }
        // Otherwise get emails from department
        elseif ($this->ticket->department_id && $this->ticket->department?->emails) {
            $emails = $emails->merge($this->ticket->department->emails);
        }

        // Send notification to collected emails
        if ($emails->isNotEmpty()) {
            Notification::route('mail', $emails->toArray())
                ->notify(new TicketAssigned($this->ticket));
        }
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
