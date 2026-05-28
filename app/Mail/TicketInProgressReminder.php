<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketInProgressReminder extends Mailable
{
    use Queueable, SerializesModels;

    public int $daysInProgress;

    public function __construct(
        public Ticket $ticket,
        public User   $assignee
    ) {
        $this->daysInProgress = (int) $ticket->assigned_at->diffInDays(now());
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[REMINDER] [{$this->ticket->ticket_number}] Ticket In Progress for {$this->daysInProgress} Days – {$this->ticket->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ticket-inprogress-reminder',
        );
    }
}
