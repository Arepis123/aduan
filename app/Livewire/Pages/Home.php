<?php

namespace App\Livewire\Pages;

use App\Models\Ticket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Home - Sistem Aduan')]
class Home extends Component
{
    public function render()
    {
        return view('livewire.pages.home', [
            'openTickets' => Ticket::whereIn('status', ['open', 'in_progress', 'pending'])->count(),
            'resolvedTickets' => Ticket::where('status', 'resolved')->count(),
        ]);
    }
}
