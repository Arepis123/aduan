<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketOverdueReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public User   $assignee
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[REMINDER] [{$this->ticket->ticket_number}] Unresolved Complaint - {$this->ticket->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ticket-overdue-reminder',
        );
    }
}
