<?php

namespace App\Livewire\Staff\Tickets;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
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
    public ?int $assigned_to = null;

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->priority = '';
        $this->department_id = null;
        $this->assigned_to = null;
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();

        $query = Ticket::with(['department', 'category', 'assignedAgent']);

        // Agents can only see their department's tickets
        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('department_id', $user->department_id);
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

        if ($this->assigned_to === 0) {
            $query->whereNull('user_id');
        } elseif ($this->assigned_to) {
            $query->where('user_id', $this->assigned_to);
        }

        $tickets = $query->orderBy($this->sortBy, $this->sortDirection)->paginate(20);

        return view('livewire.staff.tickets.ticket-list', [
            'tickets' => $tickets,
            'departments' => Department::active()->get(),
            'agents' => User::where('role', 'agent')->orWhere('role', 'admin')->get(),
            'isAdmin' => $isAdmin,
        ]);
    }
}
