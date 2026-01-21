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

        // Agents can only see their department's tickets or assigned tickets
        if (!$isAdmin) {
            $ticketQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('department_id', $user->department_id);
            });
        }

        $stats = [
            'total' => (clone $ticketQuery)->count(),
            'open' => (clone $ticketQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $ticketQuery)->where('status', 'in_progress')->count(),
            'pending' => (clone $ticketQuery)->where('status', 'pending')->count(),
            'resolved' => (clone $ticketQuery)->where('status', 'resolved')->count(),
            'my_assigned' => Ticket::where('user_id', $user->id)->whereIn('status', ['open', 'in_progress', 'pending'])->count(),
            'unassigned' => $isAdmin
                ? Ticket::whereNull('user_id')->whereIn('status', ['open', 'in_progress', 'pending'])->count()
                : Ticket::whereNull('user_id')->where('department_id', $user->department_id)->whereIn('status', ['open', 'in_progress', 'pending'])->count(),
        ];

        $recentTickets = (clone $ticketQuery)
            ->with(['department', 'category', 'assignedAgent'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.staff.dashboard', [
            'stats' => $stats,
            'recentTickets' => $recentTickets,
            'isAdmin' => $isAdmin,
        ]);
    }
}
