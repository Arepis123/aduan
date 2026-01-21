<?php

namespace App\Livewire\Pages;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Services\MathCaptchaService;
use App\Traits\WithSecurityProtection;
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
        $this->captchaAnswer = '';
        $this->showReplyForm = false;
        $this->refreshCaptcha();
        $this->ticket->refresh();
        $this->ticket->load('publicReplies.user');
    }

    public function render()
    {
        return view('livewire.pages.ticket-status');
    }
}
