<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dueDate = $this->ticket->due_date?->format('d M Y');

        return (new MailMessage)
            ->subject("[{$this->ticket->ticket_number}] New Ticket Assigned: {$this->ticket->subject}")
            ->greeting('New Ticket Assigned')
            ->line("A new ticket has been assigned to your department/unit.")
            ->line("**Ticket Number:** {$this->ticket->ticket_number}")
            ->line("**Subject:** {$this->ticket->subject}")
            ->line("**Priority:** " . ucfirst($this->ticket->priority))
            ->line("**Due Date:** {$dueDate}")
            ->line("---")
            ->line("**Requester:** {$this->ticket->requester_name}")
            ->line("**Email:** {$this->ticket->requester_email}")
            ->line("**Phone:** " . ($this->ticket->requester_phone ?? 'N/A'))
            ->line("---")
            ->line("**Description:**")
            ->line($this->ticket->description)
            ->line("---")
            ->line("Please resolve this ticket within 7 days from assignment.")
            ->action('View Ticket', route('staff.tickets.show', $this->ticket))
            ->line('Thank you.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
        ];
    }
}
