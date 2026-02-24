<?php

namespace App\Livewire\Staff\Tickets;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketReply;
use App\Models\Unit;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketClosed;
use App\Notifications\TicketReplyFromStaff;
use App\Notifications\TicketResolved;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class TicketDetail extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public ?int $assignDepartment = null;
    public ?int $assignUnit = null;
    public string $newStatus = '';
    public string $newPriority = '';

    // Reply form
    public string $replyMessage = '';
    public bool $isInternalNote = false;

    // Close ticket modal
    public bool $showCloseModal = false;
    public string $closingRemark = '';
    public array $closingAttachments = [];
    public array $newClosingAttachments = [];

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
        if ($this->newStatus === 'closed' && !Auth::user()->isAdmin()) {
            return;
        }

        // If closing, open the modal instead of directly updating
        if ($this->newStatus === 'closed') {
            $this->showCloseModal = true;
            return;
        }

        $oldStatus = $this->ticket->status;
        $data = ['status' => $this->newStatus];

        if ($this->newStatus === 'resolved') {
            $data['resolved_at'] = now();
        }

        $this->ticket->update($data);
        $this->ticket->refresh();

        // Send notification to requester on status change
        if ($oldStatus !== $this->newStatus) {
            $this->sendStatusNotification();
        }
    }

    public function updatedNewClosingAttachments(): void
    {
        $this->validate([
            'newClosingAttachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif',
        ]);

        $this->closingAttachments = array_merge($this->closingAttachments, $this->newClosingAttachments);
        $this->newClosingAttachments = [];
    }

    public function removeClosingAttachment($index): void
    {
        unset($this->closingAttachments[$index]);
        $this->closingAttachments = array_values($this->closingAttachments);
    }

    public function closeTicket(): void
    {
        if (!Auth::user()->isAdmin()) {
            return;
        }

        $this->validate([
            'closingRemark' => 'required|string|min:5',
            'closingAttachments' => 'array|max:5',
            'closingAttachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif',
        ]);

        $oldStatus = $this->ticket->status;

        $this->ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closing_remark' => $this->closingRemark,
        ]);

        // Handle file attachments
        foreach ($this->closingAttachments as $attachment) {
            $detectedMime = $this->verifyFileContent($attachment);
            if (!$detectedMime) {
                continue;
            }

            $extension = $attachment->getClientOriginalExtension();
            $randomFilename = bin2hex(random_bytes(16)) . '.' . $extension;

            $path = $attachment->storeAs('attachments/' . $this->ticket->id, $randomFilename, 'public');

            TicketAttachment::create([
                'ticket_id' => $this->ticket->id,
                'filename' => $randomFilename,
                'original_filename' => $this->sanitizeFilename($attachment->getClientOriginalName()),
                'path' => $path,
                'mime_type' => $detectedMime,
                'size' => $attachment->getSize(),
            ]);
        }

        $this->ticket->refresh();
        $this->ticket->load('attachments');
        $this->newStatus = 'closed';
        $this->showCloseModal = false;
        $this->closingRemark = '';
        $this->closingAttachments = [];

        if ($oldStatus !== 'closed') {
            $this->sendStatusNotification();
        }
    }

    public function cancelClose(): void
    {
        $this->showCloseModal = false;
        $this->closingRemark = '';
        $this->closingAttachments = [];
        $this->newStatus = $this->ticket->status;
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
        if (!auth()->user()->isAdmin()) {
            return;
        }

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

    private function verifyFileContent($file): ?string
    {
        $allowedTypes = [
            'image/jpeg' => ["\xFF\xD8\xFF"],
            'image/png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
            'image/gif' => ["GIF87a", "GIF89a"],
            'application/pdf' => ["%PDF"],
            'application/msword' => ["\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ["PK\x03\x04"],
        ];

        try {
            $handle = fopen($file->getRealPath(), 'rb');
            if (!$handle) {
                return null;
            }

            $header = fread($handle, 8);
            fclose($handle);

            if ($header === false || strlen($header) < 4) {
                return null;
            }

            foreach ($allowedTypes as $mimeType => $signatures) {
                foreach ($signatures as $signature) {
                    if (str_starts_with($header, $signature)) {
                        return $mimeType;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = basename($filename);
        $filename = preg_replace('/[^\w\-\.\s]/', '_', $filename);

        if (strlen($filename) > 255) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($name, 0, 250 - strlen($ext)) . '.' . $ext;
        }

        return $filename;
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
