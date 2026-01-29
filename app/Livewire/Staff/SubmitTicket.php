<?php

namespace App\Livewire\Staff;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Notifications\TicketCreated;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Submit Internal Ticket - Sistem Aduan CLAB')]
class SubmitTicket extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public string $subject = '';

    #[Validate('required|string|min:20')]
    public string $description = '';

    #[Validate('required|in:low,medium,high,urgent')]
    public string $priority = 'medium';

    #[Validate([
        'attachments' => 'array|max:5',
        'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif'
    ])]
    public array $attachments = [];

    // Temporary property for new file uploads
    public array $newAttachments = [];

    public ?Ticket $submittedTicket = null;

    public function updatedNewAttachments(): void
    {
        // Merge new uploads with existing attachments
        $this->attachments = array_merge($this->attachments, $this->newAttachments);
        $this->newAttachments = []; // Clear temporary array

        // Validate after merging
        $this->validateOnly('attachments');
        $this->validateOnly('attachments.*');
    }

    public function submit(): void
    {
        $this->validate();

        $user = auth()->user();

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'requester_name' => $user->name,
            'requester_email' => $user->email,
            'requester_phone' => $user->phone,
            'requester_type' => 'internal',
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => 'open',
        ]);

        // Handle file attachments
        foreach ($this->attachments as $attachment) {
            $detectedMime = $this->verifyFileContent($attachment);
            if (!$detectedMime) {
                continue;
            }

            $extension = $attachment->getClientOriginalExtension();
            $randomFilename = bin2hex(random_bytes(16)) . '.' . $extension;

            $path = $attachment->storeAs('attachments/' . $ticket->id, $randomFilename, 'public');

            TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'filename' => $randomFilename,
                'original_filename' => $this->sanitizeFilename($attachment->getClientOriginalName()),
                'path' => $path,
                'mime_type' => $detectedMime,
                'size' => $attachment->getSize(),
            ]);
        }

        // Send confirmation email to requester (internal staff)
        Notification::route('mail', $ticket->requester_email)
            ->notify(new TicketCreated($ticket));

        $this->submittedTicket = $ticket;
        $this->dispatch('ticket-submitted');
    }

    public function removeAttachment($index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
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
        return view('livewire.staff.submit-ticket');
    }
}
