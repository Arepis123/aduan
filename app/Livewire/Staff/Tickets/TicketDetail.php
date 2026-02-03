<?php

namespace App\Livewire\Staff\Tickets;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\Unit;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketClosed;
use App\Notifications\TicketReplyFromStaff;
use App\Notifications\TicketResolved;
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

    // Reply form
    public string $replyMessage = '';
    public bool $isInternalNote = false;

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket->load(['department', 'unit', 'category', 'attachments', 'replies.user']);
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
        $hasAssignment = $this->assignDepartment || $this->assignUnit;
        $isFirstAssignment = !$this->ticket->assigned_at && $hasAssignment;
        $isOpenStatus = $this->ticket->status === 'open';

        $data = [
            'department_id' => $this->assignDepartment ?: null,
            'unit_id' => $this->assignUnit ?: null,
        ];

        // Set assigned_at on first assignment
        if ($isFirstAssignment) {
            $data['assigned_at'] = now();
        }

        // Auto-change status to "in_progress" when assigning from "open" status
        if ($isOpenStatus && $hasAssignment) {
            $data['status'] = 'in_progress';
            $this->newStatus = 'in_progress';
        }

        $this->ticket->update($data);
        $this->ticket->refresh();
        $this->ticket->load(['department', 'unit']);

        // Send email notification
        if ($hasAssignment) {
            $this->sendAssignmentNotification();
        }
    }

    protected function sendAssignmentNotification(): void
    {
        $toEmails = collect();
        $ccEmails = collect();

        // Load relationships for email collection
        $this->ticket->load(['unit.department.sector', 'department.sector']);

        // Get TO emails from unit if assigned
        if ($this->ticket->unit_id && $this->ticket->unit?->emails) {
            $toEmails = $toEmails->merge($this->ticket->unit->emails);

            // CC to Department PIC
            if ($this->ticket->unit->department?->emails) {
                $ccEmails = $ccEmails->merge($this->ticket->unit->department->emails);
            }

            // CC to Sector PIC
            if ($this->ticket->unit->department?->sector?->emails) {
                $ccEmails = $ccEmails->merge($this->ticket->unit->department->sector->emails);
            }
        }
        // Otherwise get TO emails from department
        elseif ($this->ticket->department_id && $this->ticket->department?->emails) {
            $toEmails = $toEmails->merge($this->ticket->department->emails);

            // CC to Sector PIC
            if ($this->ticket->department->sector?->emails) {
                $ccEmails = $ccEmails->merge($this->ticket->department->sector->emails);
            }
        }

        // Remove duplicates and ensure CC doesn't include TO emails
        $ccEmails = $ccEmails->unique()->diff($toEmails)->values();

        // Send notification to collected emails
        if ($toEmails->isNotEmpty()) {
            Notification::route('mail', $toEmails->toArray())
                ->notify(new TicketAssigned($this->ticket, $ccEmails->toArray()));
        }
    }

    public function updatedAssignDepartment($value): void
    {
        // Reset unit when department changes
        $this->assignUnit = null;
    }

    public function updateStatus(): void
    {
        $oldStatus = $this->ticket->status;
        $data = ['status' => $this->newStatus];

        if ($this->newStatus === 'resolved') {
            $data['resolved_at'] = now();
        } elseif ($this->newStatus === 'closed') {
            $data['closed_at'] = now();
        }

        $this->ticket->update($data);
        $this->ticket->refresh();

        // Send notification to requester on status change
        if ($oldStatus !== $this->newStatus) {
            $this->sendStatusNotification();
        }
    }

    protected function sendStatusNotification(): void
    {
        $email = $this->ticket->requester_email;

        if ($this->ticket->status === 'resolved') {
            Notification::route('mail', $email)
                ->notify(new TicketResolved($this->ticket));
        } elseif ($this->ticket->status === 'closed') {
            Notification::route('mail', $email)
                ->notify(new TicketClosed($this->ticket));
        }
    }

    public function updatePriority(): void
    {
        $this->ticket->update(['priority' => $this->newPriority]);
        $this->ticket->refresh();
    }

    public function submitReply(): void
    {
        $this->validate([
            'replyMessage' => 'required|string|min:10',
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => auth()->id(),
            'message' => $this->replyMessage,
            'is_client_reply' => false,
            'is_internal_note' => $this->isInternalNote,
        ]);

        // Send email notification to requester (only for non-internal notes)
        if (!$this->isInternalNote) {
            $this->sendReplyNotification($reply);
        }

        $this->replyMessage = '';
        $this->isInternalNote = false;
        $this->ticket->refresh();
        $this->ticket->load('replies.user');
    }

    protected function sendReplyNotification(TicketReply $reply): void
    {
        $email = $this->ticket->requester_email;

        if ($email) {
            Notification::route('mail', $email)
                ->notify(new TicketReplyFromStaff($this->ticket, $reply));
        }
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
