<x-mail::message>
# New Reply to Your Ticket

Dear {{ $ticket->requester_name }},

A new reply has been added to your ticket.

**Ticket:** {{ $ticket->ticket_number }} - {{ $ticket->subject }}

---

**Reply from {{ $reply->user?->name ?? 'Staff' }}:**

{{ $reply->message }}

---

<x-mail::button :url="route('ticket.status', $ticket->ticket_number)">
View Full Conversation
</x-mail::button>

You can reply to this ticket by visiting the link above.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
