<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyFromRequester extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public TicketReply $reply,
        public array $ccEmails = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("[{$this->ticket->ticket_number}] New Reply from Requester: {$this->ticket->subject}")
            ->greeting('New Reply Received')
            ->line("A new reply has been received for ticket **{$this->ticket->ticket_number}**.")
            ->line('---')
            ->line("**From:** {$this->ticket->requester_name} ({$this->ticket->requester_email})")
            ->line("**Subject:** {$this->ticket->subject}")
            ->line('---')
            ->line('**Reply Message:**')
            ->line($this->reply->message)
            ->line('---')
            ->action('View Ticket', url("/staff/tickets/{$this->ticket->id}"))
            ->line('Please respond to the requester as soon as possible.');

        if (!empty($this->ccEmails)) {
            $mail->cc($this->ccEmails);
        }

        return $mail;
    }
}
