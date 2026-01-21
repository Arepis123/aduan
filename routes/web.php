<?php

use App\Livewire\Pages\CheckStatus;
use App\Livewire\Pages\Home;
use App\Livewire\Pages\SubmitTicket;
use App\Livewire\Pages\TicketStatus;
use App\Livewire\Staff\Dashboard;
use App\Livewire\Staff\Tickets\TicketDetail;
use App\Livewire\Staff\Tickets\TicketList;
use App\Livewire\Admin\CategoryManagement;
use App\Livewire\Admin\DepartmentManagement;
use App\Livewire\Admin\SectorManagement;
use App\Livewire\Admin\UnitManagement;
use App\Livewire\Admin\UserManagement;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', Home::class)->name('home');
Route::get('/submit', SubmitTicket::class)->name('submit');
Route::get('/check', CheckStatus::class)->name('check');
Route::get('/ticket/{ticketNumber}', TicketStatus::class)->name('ticket.status');

// Staff routes
Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/tickets', TicketList::class)->name('tickets.index');
    Route::get('/tickets/{ticket}', TicketDetail::class)->name('tickets.show');

    // Admin only routes
    Route::middleware(['can:users.view'])->group(function () {
        Route::get('/users', UserManagement::class)->name('users.index');
        Route::get('/sectors', SectorManagement::class)->name('sectors.index');
        Route::get('/departments', DepartmentManagement::class)->name('departments.index');
        Route::get('/units', UnitManagement::class)->name('units.index');
        Route::get('/categories', CategoryManagement::class)->name('categories.index');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
