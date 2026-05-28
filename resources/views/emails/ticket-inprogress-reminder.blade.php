<x-mail::message>
# Ticket In Progress Reminder

Dear {{ $assignee->name }},

This is a reminder that the following ticket has been **In Progress for {{ $daysInProgress }} {{ $daysInProgress === 1 ? 'day' : 'days' }}** and is still awaiting resolution.

<x-mail::panel>
**Ticket Number:** {{ $ticket->ticket_number }}

**Subject:** {{ $ticket->subject }}

**Complainant:** {{ $ticket->requester_name }}
@if($ticket->complainant_company)
**Company:** {{ $ticket->complainant_company }}
@endif

**Priority:** {{ ucfirst($ticket->priority) }}

**In Progress Since:** {{ $ticket->assigned_at->format('d M Y, h:i A') }}
</x-mail::panel>

Please resolve this ticket at your earliest convenience.

<x-mail::button :url="route('staff.tickets.show', $ticket)">
View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
