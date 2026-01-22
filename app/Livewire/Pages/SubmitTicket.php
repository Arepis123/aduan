<?php

namespace App\Livewire\Pages;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Services\MathCaptchaService;
use App\Traits\WithSecurityProtection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.public')]
#[Title('Submit Ticket - Sistem Aduan CLAB')]
class SubmitTicket extends Component
{
    use WithFileUploads, WithSecurityProtection;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public ?string $phone = '';

    #[Validate('required|string|max:255')]
    public string $subject = '';

    #[Validate('required|string|min:20')]
    public string $description = '';

    #[Validate('required|in:low,medium,high,urgent')]
    public string $priority = 'medium';

    #[Validate(['attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif'])]
    public array $attachments = [];

    // Math Captcha
    public string $captchaQuestion = '';
    public string $captchaHash = '';
    public string $captchaAnswer = '';

    public ?Ticket $submittedTicket = null;

    public function mount(): void
    {
        $this->mountWithSecurityProtection();
        $this->refreshCaptcha();
    }

    public function refreshCaptcha(): void
    {
        $captcha = MathCaptchaService::generate();
        $this->captchaQuestion = $captcha['question'];
        $this->captchaHash = $captcha['hash'];
        $this->captchaAnswer = '';
    }

    public function validateForm(): void
    {
        // Validate form fields first (before showing captcha modal)
        $this->validate();

        // Refresh captcha for security
        $this->refreshCaptcha();

        // Open the captcha modal
        $this->modal('captcha-modal')->show();
    }

    public function submit(): void
    {
        // Rate limit: 5 submissions per hour per IP
        $this->checkRateLimit('ticket-submission', 5, 60);

        // Honeypot validation
        $this->validateHoneypot();

        // Validate form fields
        $this->validate();

        // Validate math captcha
        if (!MathCaptchaService::verify($this->captchaHash, $this->captchaAnswer)) {
            $this->refreshCaptcha();
            $this->addError('captchaAnswer', 'Incorrect answer. Please try again.');
            return;
        }

        // Close the modal
        $this->modal('captcha-modal')->close();

        $ticket = Ticket::create([
            'requester_name' => $this->name,
            'requester_email' => $this->email,
            'requester_phone' => $this->phone,
            'requester_type' => 'external',
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => 'open',
        ]);

        // Handle file attachments
        foreach ($this->attachments as $attachment) {
            // Verify actual file content matches expected MIME type (security check)
            $detectedMime = $this->verifyFileContent($attachment);
            if (!$detectedMime) {
                continue; // Skip invalid files
            }

            // Generate random filename to prevent path traversal attacks
            $extension = $attachment->getClientOriginalExtension();
            $randomFilename = bin2hex(random_bytes(16)) . '.' . $extension;

            // Store with random filename
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

        $this->submittedTicket = $ticket;
        $this->dispatch('ticket-submitted');
    }

    public function removeAttachment($index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    /**
     * Verify file content matches expected MIME type by reading file bytes.
     * This prevents users from renaming malicious files (e.g., .php to .jpg)
     */
    private function verifyFileContent($file): ?string
    {
        // Allowed MIME types and their magic bytes signatures
        $allowedTypes = [
            // Images
            'image/jpeg' => ["\xFF\xD8\xFF"],
            'image/png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
            'image/gif' => ["GIF87a", "GIF89a"],
            // PDF
            'application/pdf' => ["%PDF"],
            // Microsoft Word (DOC - OLE format)
            'application/msword' => ["\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"],
            // Microsoft Word (DOCX - ZIP-based)
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ["PK\x03\x04"],
        ];

        try {
            $handle = fopen($file->getRealPath(), 'rb');
            if (!$handle) {
                return null;
            }

            // Read first 8 bytes for signature detection
            $header = fread($handle, 8);
            fclose($handle);

            if ($header === false || strlen($header) < 4) {
                return null;
            }

            // Check against known signatures
            foreach ($allowedTypes as $mimeType => $signatures) {
                foreach ($signatures as $signature) {
                    if (str_starts_with($header, $signature)) {
                        return $mimeType;
                    }
                }
            }

            // No valid signature found
            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Sanitize filename to prevent XSS and other attacks
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove any directory path components
        $filename = basename($filename);

        // Remove or replace dangerous characters
        $filename = preg_replace('/[^\w\-\.\s]/', '_', $filename);

        // Limit length
        if (strlen($filename) > 255) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($name, 0, 250 - strlen($ext)) . '.' . $ext;
        }

        return $filename;
    }

    public function render()
    {
        return view('livewire.pages.submit-ticket');
    }
}
