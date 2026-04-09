<?php

namespace App\Livewire\Staff;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard - Sistem Aduan CLAB')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();

        // Base query
        $ticketQuery = Ticket::query();

        // Agents can only see tickets in their department or assigned to them
        if (!$isAdmin) {
            $ticketQuery->where(function ($q) use ($user) {
                $q->where('department_id', $user->department_id)
                  ->orWhereHas('assignees', fn($q) => $q->where('users.id', $user->id));
            });
        }

        $stats = [
            'total'       => (clone $ticketQuery)->count(),
            'open'        => (clone $ticketQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $ticketQuery)->where('status', 'in_progress')->count(),
            'pending'     => (clone $ticketQuery)->where('status', 'pending')->count(),
            'resolved'    => (clone $ticketQuery)->where('status', 'resolved')->count(),
            'my_assigned' => Ticket::whereHas('assignees', fn($q) => $q->where('users.id', $user->id))
                ->whereIn('status', ['open', 'in_progress', 'pending'])->count(),
            'unassigned'  => $isAdmin
                ? Ticket::doesntHave('assignees')->whereIn('status', ['open', 'in_progress', 'pending'])->count()
                : Ticket::doesntHave('assignees')->where('department_id', $user->department_id)->whereIn('status', ['open', 'in_progress', 'pending'])->count(),
        ];

        $recentTickets = (clone $ticketQuery)
            ->with(['department', 'category', 'assignedAgent'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $slaTickets = (clone $ticketQuery)
            ->whereIn('status', ['open', 'in_progress', 'pending'])
            ->whereNotNull('assigned_at')
            ->with(['department', 'assignedAgent'])
            ->orderBy('assigned_at', 'asc')
            ->get();

        return view('livewire.staff.dashboard', [
            'stats' => $stats,
            'recentTickets' => $recentTickets,
            'slaTickets' => $slaTickets,
            'isAdmin' => $isAdmin,
        ]);
    }
}
