<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification
{
    public function __construct(
        public Ticket $ticket
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->ticket->ticket_number}] Ticket Received: {$this->ticket->subject}")
            ->greeting("Hello {$this->ticket->requester_name},")
            ->line("Thank you for contacting us. Your ticket has been received and logged in our system.")
            ->line("---")
            ->line("**Ticket Number:** {$this->ticket->ticket_number}")
            ->line("**Subject:** {$this->ticket->subject}")
            ->line("**Priority:** " . ucfirst($this->ticket->priority))
            ->line("**Submitted:** {$this->ticket->created_at->format('d M Y, h:i A')}")
            ->line("---")
            ->line("**Your Message:**")
            ->line($this->ticket->description)
            ->line("---")
            ->line("Please save your ticket number for future reference. You can use it to check the status of your ticket.")
            ->action('Check Ticket Status', route('ticket.status', $this->ticket->ticket_number))
            ->line("We will review your ticket and get back to you as soon as possible.")
            ->salutation("Best regards,\n" . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
        ];
    }
}
