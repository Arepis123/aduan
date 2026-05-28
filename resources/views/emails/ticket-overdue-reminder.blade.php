<x-mail::message>
# Unresolved Complaint Reminder

Dear {{ $assignee->name }},

This is a reminder that the following complaint has been open for **{{ $ticket->created_at->diffInDays(now()) }} days** and has not been resolved yet.

<x-mail::panel>
**Ticket Number:** {{ $ticket->ticket_number }}

**Subject:** {{ $ticket->subject }}

**Complainant:** {{ $ticket->requester_name }}
@if($ticket->complainant_company)
**Company:** {{ $ticket->complainant_company }}
@endif

**Priority:** {{ ucfirst($ticket->priority) }}

**Status:** {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}

**Submitted On:** {{ $ticket->created_at->format('d M Y, h:i A') }}
</x-mail::panel>

Please take immediate action to resolve or update the status of this complaint.

<x-mail::button :url="route('staff.tickets.show', $ticket)">
View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
