<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\View\View;

class TicketStatusController extends Controller
{
    public function show(string $ticketNumber): View
    {
        $ticket = Ticket::where('ticket_number', $ticketNumber)
            ->with(['category', 'department', 'sector'])
            ->firstOrFail();

        return view('ticket.status', compact('ticket'));
    }
}
