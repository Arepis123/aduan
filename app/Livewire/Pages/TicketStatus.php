<?php

namespace App\Livewire\Pages;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Notifications\TicketReplyFromRequester;
use App\Services\MathCaptchaService;
use App\Traits\WithSecurityProtection;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.public')]
class TicketStatus extends Component
{
    use WithSecurityProtection;

    public Ticket $ticket;

    #[Validate('required|string|min:10')]
    public string $reply = '';

    public bool $showReplyForm = false;

    // Math Captcha
    public string $captchaQuestion = '';
    public string $captchaHash = '';
    public string $captchaAnswer = '';

    public function mount(string $ticketNumber): void
    {
        $this->mountWithSecurityProtection();
        $this->refreshCaptcha();

        $this->ticket = Ticket::where('ticket_number', $ticketNumber)
            ->with(['department', 'category', 'assignedAgent', 'publicReplies.user', 'attachments'])
            ->firstOrFail();
    }

    public function refreshCaptcha(): void
    {
        $captcha = MathCaptchaService::generate();
        $this->captchaQuestion = $captcha['question'];
        $this->captchaHash = $captcha['hash'];
        $this->captchaAnswer = '';
    }

    public function validateReply(): void
    {
        // Validate the reply first
        $this->validate();

        // Refresh captcha for security
        $this->refreshCaptcha();

        // Open the captcha modal
        $this->modal('reply-captcha-modal')->show();
    }

    public function getTitle(): string
    {
        return $this->ticket->ticket_number . ' - Sistem Aduan CLAB';
    }

    public function submitReply(): void
    {
        // Rate limit: 10 replies per hour per IP
        $this->checkRateLimit('ticket-reply', 10, 60);

        // Honeypot validation
        $this->validateHoneypot();

        $this->validate();

        // Validate math captcha
        if (!MathCaptchaService::verify($this->captchaHash, $this->captchaAnswer)) {
            $this->refreshCaptcha();
            $this->addError('captchaAnswer', 'Incorrect answer. Please try again.');
            return;
        }

        // Close the modal
        $this->modal('reply-captcha-modal')->close();

        $reply = TicketReply::create([
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

        // Send email notification to assigned PICs
        $this->sendReplyNotification($reply);

        $this->reply = '';
        $this->captchaAnswer = '';
        $this->showReplyForm = false;
        $this->refreshCaptcha();
        $this->ticket->refresh();
        $this->ticket->load('publicReplies.user');
    }

    protected function sendReplyNotification(TicketReply $reply): void
    {
        $this->ticket->load(['unit.department.sector', 'department.sector']);

        $toEmails = collect();
        $ccEmails = collect();

        // Get TO emails from unit if assigned
        if ($this->ticket->unit_id && $this->ticket->unit?->emails) {
            $toEmails = $toEmails->merge($this->ticket->unit->emails);

            if ($this->ticket->unit->department?->emails) {
                $ccEmails = $ccEmails->merge($this->ticket->unit->department->emails);
            }
            if ($this->ticket->unit->department?->sector?->emails) {
                $ccEmails = $ccEmails->merge($this->ticket->unit->department->sector->emails);
            }
        }
        // Otherwise get TO emails from department
        elseif ($this->ticket->department_id && $this->ticket->department?->emails) {
            $toEmails = $toEmails->merge($this->ticket->department->emails);

            if ($this->ticket->department->sector?->emails) {
                $ccEmails = $ccEmails->merge($this->ticket->department->sector->emails);
            }
        }

        $ccEmails = $ccEmails->unique()->diff($toEmails)->values();

        if ($toEmails->isNotEmpty()) {
            Notification::route('mail', $toEmails->toArray())
                ->notify(new TicketReplyFromRequester($this->ticket, $reply, $ccEmails->toArray()));
        }
    }

    public function render()
    {
        return view('livewire.pages.ticket-status');
    }
}
