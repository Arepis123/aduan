<?php

namespace App\Livewire\Staff;

use App\Models\Department;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $slaTickets = (clone $ticketQuery)
            ->whereIn('status', ['open', 'in_progress', 'pending'])
            ->whereNotNull('assigned_at')
            ->with(['department', 'assignedAgent'])
            ->orderBy('assigned_at', 'asc')
            ->get();

        // Chart 1: Tickets by Status (Doughnut)
        $statusChart = [
            'labels' => ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'],
            'data'   => [
                $stats['open'],
                $stats['in_progress'],
                $stats['pending'],
                (clone $ticketQuery)->where('status', 'resolved')->count(),
                (clone $ticketQuery)->where('status', 'closed')->count(),
            ],
            'colors' => ['#3b82f6', '#eab308', '#f97316', '#22c55e', '#71717a'],
        ];

        // Chart 2: Tickets by Department (Horizontal Bar) — admin sees all, staff sees their dept
        $deptQuery = Ticket::select('department_id', DB::raw('count(*) as total'))
            ->whereNotNull('department_id')
            ->groupBy('department_id')
            ->with('department')
            ->orderByDesc('total')
            ->limit(8);

        if (!$isAdmin) {
            $deptQuery->where('department_id', $user->department_id);
        }

        $deptData      = $deptQuery->get();
        $deptChart     = [
            'labels' => $deptData->map(fn($r) => $r->department?->name ?? 'Unknown')->toArray(),
            'data'   => $deptData->pluck('total')->toArray(),
        ];

        return view('livewire.staff.dashboard', [
            'stats'        => $stats,
            'slaTickets'   => $slaTickets,
            'isAdmin'      => $isAdmin,
            'statusChart'  => $statusChart,
            'deptChart'    => $deptChart,
        ]);
    }
}
