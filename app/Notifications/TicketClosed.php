<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketClosed extends Notification
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
            ->subject("[{$this->ticket->ticket_number}] Ticket Closed: {$this->ticket->subject}")
            ->greeting("Hello {$this->ticket->requester_name},")
            ->line("Your ticket has been closed.")
            ->line("---")
            ->line("**Ticket Number:** {$this->ticket->ticket_number}")
            ->line("**Subject:** {$this->ticket->subject}")
            ->line("**Status:** Closed")
            ->line("**Closed On:** {$this->ticket->closed_at->format('d M Y, h:i A')}")
            ->line("---")
            ->line("If you need further assistance, please don't hesitate to submit a new ticket.")
            ->action('Submit New Ticket', route('submit'))
            ->line("Thank you for using our service.")
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
