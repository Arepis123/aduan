<x-mail::message>
# Ticket Received

Dear {{ $ticket->requester_name }},

Thank you for contacting us. Your ticket has been received and our team will review it shortly.

**Ticket Details:**

- **Ticket Number:** {{ $ticket->ticket_number }}
- **Subject:** {{ $ticket->subject }}
- **Department:** {{ $ticket->department?->name ?? 'General' }}
- **Priority:** {{ ucfirst($ticket->priority) }}
- **Status:** {{ ucfirst($ticket->status) }}

<x-mail::button :url="route('ticket.status', $ticket->ticket_number)">
View Ticket Status
</x-mail::button>

You can use your ticket number and email address to check the status of your ticket at any time.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
