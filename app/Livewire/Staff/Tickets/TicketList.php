<?php

namespace App\Livewire\Staff\Tickets;

use App\Models\Department;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Tickets - Sistem Aduan CLAB')]
class TicketList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $priority = '';

    #[Url]
    public ?int $department_id = null;

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->priority = '';
        $this->department_id = null;
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();

        $query = Ticket::with(['department', 'category', 'assignedAgent']);

        // Agents can only see their department's tickets or tickets assigned to them
        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('department_id', $user->department_id)
                  ->orWhereHas('assignees', fn($q) => $q->where('users.id', $user->id));
            });
        }

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('ticket_number', 'like', '%' . $this->search . '%')
                  ->orWhere('subject', 'like', '%' . $this->search . '%')
                  ->orWhere('requester_name', 'like', '%' . $this->search . '%')
                  ->orWhere('requester_email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->priority) {
            $query->where('priority', $this->priority);
        }

        if ($this->department_id) {
            $query->where('department_id', $this->department_id);
        }

        if ($this->sortBy === 'days_to_resolve') {
            // Push NULL rows to the bottom regardless of sort direction
            $query->orderByRaw("
                CASE
                    WHEN assigned_at IS NOT NULL AND resolved_at IS NOT NULL
                        THEN DATEDIFF(resolved_at, assigned_at)
                    WHEN assigned_at IS NOT NULL
                        THEN DATEDIFF(NOW(), assigned_at)
                    ELSE NULL
                END IS NULL ASC
            ")->orderByRaw("
                CASE
                    WHEN assigned_at IS NOT NULL AND resolved_at IS NOT NULL
                        THEN DATEDIFF(resolved_at, assigned_at)
                    WHEN assigned_at IS NOT NULL
                        THEN DATEDIFF(NOW(), assigned_at)
                    ELSE NULL
                END " . $this->sortDirection
            );
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        $tickets = $query->paginate(20);

        return view('livewire.staff.tickets.ticket-list', [
            'tickets' => $tickets,
            'departments' => Department::active()->get(),
            'isAdmin' => $isAdmin,
        ]);
    }
}
