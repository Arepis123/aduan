<?php

namespace App\Livewire\Staff\Tickets;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketAttachment;
use App\Models\TicketLog;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketClosed;
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

    public array $assignUserIds = [];
    public ?int $ccDepartmentId = null;
    public ?int $ccSectorId = null;
    public bool $showUserModal = false;
    public string $newStatus = '';
    public string $newPriority = '';

    // Manual log form
    public string $logNote = '';
    public array $logAttachments = [];
    public array $newLogAttachments = [];

    // Close ticket modal
    public bool $showCloseModal = false;
    public string $closingRemark = '';
    public array $closingAttachments = [];
    public array $newClosingAttachments = [];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket->load(['sector', 'department', 'category', 'attachments', 'logs.user', 'logs.attachments', 'assignees']);
        $this->assignUserIds = $ticket->assignees->pluck('id')->toArray();
        $this->ccDepartmentId = $ticket->department_id;
        $this->ccSectorId = $ticket->sector_id;
        $this->newStatus = $ticket->status;
        $this->newPriority = $ticket->priority;
    }

    public function getTitle(): string
    {
        return $this->ticket->ticket_number . ' - Sistem Aduan CLAB';
    }

    public function updatedCcDepartmentId(?int $value): void
    {
        $this->ccSectorId = $value
            ? Department::find($value)?->sector_id
            : null;
    }

    public function updateAssignment(): void
    {
        $hasAssignment = !empty($this->assignUserIds);
        $isFirstAssignment = !$this->ticket->assigned_at && $hasAssignment;
        $isOpenStatus = $this->ticket->status === 'open';

        $this->ticket->update([
            'department_id' => $this->ccDepartmentId ?: null,
            'sector_id'     => $this->ccSectorId ?: null,
        ]);

        $this->ticket->assignees()->sync($this->assignUserIds);

        if ($isFirstAssignment) {
            $this->ticket->update(['assigned_at' => now()]);
        }

        if ($isOpenStatus && $hasAssignment) {
            $this->ticket->update(['status' => 'in_progress']);
            $this->newStatus = 'in_progress';
        }

        $this->ticket->refresh();
        $this->ticket->load(['sector', 'department', 'assignees']);

        $assigneeNames = $this->ticket->assignees->pluck('name')->join(', ') ?: 'None';
        $dept          = $this->ticket->department?->name ?? 'N/A';
        $sector        = $this->ticket->sector?->name ?? 'N/A';
        $by            = Auth::user()->name;

        $this->addSystemLog('assigned', "Ticket assigned to: {$assigneeNames}. CC — Department: {$dept}, Sector: {$sector}. By {$by}.");

        if ($isOpenStatus && $hasAssignment) {
            $this->addSystemLog('status_changed', "Status automatically changed to In Progress upon assignment by {$by}.");
        }

        if ($hasAssignment) {
            $this->sendAssignmentNotification();
        }

        $this->refreshLogs();
    }

    protected function sendAssignmentNotification(): void
    {
        $this->ticket->load(['assignees', 'department', 'sector.users']);

        $toEmails = $this->ticket->assignees->pluck('email')->filter()->values();
        $ccEmails = collect();

        if ($this->ticket->department?->emails) {
            $ccEmails = $ccEmails->merge($this->ticket->department->emails);
        }

        if ($this->ticket->sector) {
            if ($this->ticket->sector->emails) {
                $ccEmails = $ccEmails->merge($this->ticket->sector->emails);
            }

            $sectorUserEmails = $this->ticket->sector->users->pluck('email')->filter();
            $ccEmails = $ccEmails->merge($sectorUserEmails);
        }

        $ccEmails = $ccEmails->unique()->diff($toEmails)->values();

        if ($toEmails->isNotEmpty()) {
            Notification::route('mail', $toEmails->toArray())
                ->notify(new TicketAssigned($this->ticket, $ccEmails->toArray()));
        }
    }

    public function updateStatus(): void
    {
        if ($this->newStatus === 'closed' && !Auth::user()->isAdmin()) {
            return;
        }

        if ($this->newStatus === 'resolved') {
            $this->showCloseModal = true;
            return;
        }

        $oldStatus = $this->ticket->status;

        $this->ticket->update(['status' => $this->newStatus]);
        $this->ticket->refresh();

        if ($oldStatus !== $this->newStatus) {
            $oldLabel = ucfirst(str_replace('_', ' ', $oldStatus));
            $newLabel = ucfirst(str_replace('_', ' ', $this->newStatus));
            $by = Auth::user()->name;

            $this->addSystemLog('status_changed', "Status changed from {$oldLabel} to {$newLabel} by {$by}.");
            $this->sendStatusNotification();
        }

        $this->refreshLogs();
    }

    public function updatedNewLogAttachments(): void
    {
        $this->validate([
            'newLogAttachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif',
        ]);

        $this->logAttachments = array_merge($this->logAttachments, $this->newLogAttachments);
        $this->newLogAttachments = [];
    }

    public function removeLogAttachment(int $index): void
    {
        unset($this->logAttachments[$index]);
        $this->logAttachments = array_values($this->logAttachments);
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
        $this->validate([
            'closingRemark'        => 'required|string|min:5',
            'closingAttachments'   => 'array|max:5',
            'closingAttachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif',
        ]);

        $oldStatus = $this->ticket->status;

        $this->ticket->update([
            'status'         => 'resolved',
            'resolved_at'    => now(),
            'closing_remark' => $this->closingRemark,
        ]);

        $by = Auth::user()->name;
        $log = TicketLog::create([
            'ticket_id'   => $this->ticket->id,
            'user_id'     => Auth::id(),
            'type'        => 'system',
            'action'      => 'status_changed',
            'description' => "Ticket resolved by {$by}. Remark: {$this->closingRemark}",
        ]);

        foreach ($this->closingAttachments as $attachment) {
            $detectedMime = $this->verifyFileContent($attachment);
            if (!$detectedMime) {
                continue;
            }

            $extension      = $attachment->getClientOriginalExtension();
            $randomFilename = bin2hex(random_bytes(16)) . '.' . $extension;
            $path           = $attachment->storeAs('attachments/' . $this->ticket->id, $randomFilename, 'public');

            TicketAttachment::create([
                'ticket_id'         => $this->ticket->id,
                'ticket_log_id'     => $log->id,
                'filename'          => $randomFilename,
                'original_filename' => $this->sanitizeFilename($attachment->getClientOriginalName()),
                'path'              => $path,
                'mime_type'         => $detectedMime,
                'size'              => $attachment->getSize(),
            ]);
        }

        $this->ticket->refresh();
        $this->ticket->load('attachments');
        $this->newStatus      = 'resolved';
        $this->showCloseModal = false;
        $this->closingRemark  = '';
        $this->closingAttachments = [];

        if ($oldStatus !== 'resolved') {
            $this->sendStatusNotification();
        }

        $this->refreshLogs();
    }

    public function cancelClose(): void
    {
        $this->showCloseModal = false;
        $this->closingRemark  = '';
        $this->closingAttachments = [];
        $this->newStatus = $this->ticket->status;
    }

    protected function sendStatusNotification(): void
    {
        $email = $this->ticket->requester_email;

        if ($this->ticket->status === 'resolved') {
            Notification::route('mail', $email)->notify(new TicketResolved($this->ticket));
        } elseif ($this->ticket->status === 'closed') {
            Notification::route('mail', $email)->notify(new TicketClosed($this->ticket));
        }
    }

    public function updatePriority(): void
    {
        if (!auth()->user()->isAdmin()) {
            return;
        }

        $oldPriority = $this->ticket->priority;
        $this->ticket->update(['priority' => $this->newPriority]);
        $this->ticket->refresh();

        $by = Auth::user()->name;
        $this->addSystemLog('priority_changed', "Priority changed from " . ucfirst($oldPriority) . " to " . ucfirst($this->newPriority) . " by {$by}.");

        $this->refreshLogs();
    }

    public function submitLog(): void
    {
        $this->validate([
            'logNote'           => 'required|string|min:5',
            'logAttachments'    => 'array|max:5',
            'logAttachments.*'  => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif',
        ]);

        $log = TicketLog::create([
            'ticket_id'   => $this->ticket->id,
            'user_id'     => Auth::id(),
            'type'        => 'manual',
            'action'      => 'note',
            'description' => $this->logNote,
        ]);

        foreach ($this->logAttachments as $attachment) {
            $detectedMime = $this->verifyFileContent($attachment);
            if (!$detectedMime) {
                continue;
            }

            $extension      = $attachment->getClientOriginalExtension();
            $randomFilename = bin2hex(random_bytes(16)) . '.' . $extension;
            $path           = $attachment->storeAs('attachments/' . $this->ticket->id, $randomFilename, 'public');

            TicketAttachment::create([
                'ticket_id'         => $this->ticket->id,
                'ticket_log_id'     => $log->id,
                'filename'          => $randomFilename,
                'original_filename' => $this->sanitizeFilename($attachment->getClientOriginalName()),
                'path'              => $path,
                'mime_type'         => $detectedMime,
                'size'              => $attachment->getSize(),
            ]);
        }

        $this->logNote        = '';
        $this->logAttachments = [];
        $this->refreshLogs();
    }

    protected function addSystemLog(string $action, string $description): void
    {
        TicketLog::create([
            'ticket_id'   => $this->ticket->id,
            'user_id'     => Auth::id(),
            'type'        => 'system',
            'action'      => $action,
            'description' => $description,
        ]);
    }

    protected function refreshLogs(): void
    {
        $this->ticket->load('logs.user', 'logs.attachments');
    }

    private function verifyFileContent($file): ?string
    {
        $allowedTypes = [
            'image/jpeg'       => ["\xFF\xD8\xFF"],
            'image/png'        => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
            'image/gif'        => ["GIF87a", "GIF89a"],
            'application/pdf'  => ["%PDF"],
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
            $ext      = pathinfo($filename, PATHINFO_EXTENSION);
            $name     = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($name, 0, 250 - strlen($ext)) . '.' . $ext;
        }

        return $filename;
    }

    public function render()
    {
        $users       = User::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();

        return view('livewire.staff.tickets.ticket-detail', [
            'users'       => $users,
            'departments' => $departments,
        ]);
    }
}
