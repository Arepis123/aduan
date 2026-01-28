<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketResolved extends Notification
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
            ->subject("[{$this->ticket->ticket_number}] Ticket Resolved: {$this->ticket->subject}")
            ->greeting("Hello {$this->ticket->requester_name},")
            ->line("Good news! Your ticket has been resolved.")
            ->line("---")
            ->line("**Ticket Number:** {$this->ticket->ticket_number}")
            ->line("**Subject:** {$this->ticket->subject}")
            ->line("**Status:** Resolved")
            ->line("**Resolved On:** {$this->ticket->resolved_at->format('d M Y, h:i A')}")
            ->line("---")
            ->line("If you have any further questions or if the issue persists, please feel free to submit a new ticket or contact us.")
            ->action('View Ticket Details', route('ticket.status', $this->ticket->ticket_number))
            ->line("Thank you for your patience.")
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
