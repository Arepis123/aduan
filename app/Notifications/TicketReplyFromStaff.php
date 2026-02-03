<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyFromStaff extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public TicketReply $reply
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->ticket->ticket_number}] New Reply: {$this->ticket->subject}")
            ->greeting("Hello {$this->ticket->requester_name},")
            ->line("You have received a new reply for your ticket **{$this->ticket->ticket_number}**.")
            ->line('---')
            ->line("**Subject:** {$this->ticket->subject}")
            ->line("**Status:** " . ucfirst(str_replace('_', ' ', $this->ticket->status)))
            ->line('---')
            ->line('**Reply from Staff:**')
            ->line($this->reply->message)
            ->line('---')
            ->action('View Ticket', url("/ticket/{$this->ticket->ticket_number}"))
            ->line('You can reply to this ticket by clicking the button above.')
            ->salutation('Thank you for using Sistem Aduan CLAB');
    }
}
