<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\TicketStatusController;
use App\Livewire\Staff\Dashboard;
use App\Livewire\Staff\Settings;
use App\Livewire\Staff\SubmitTicket as StaffSubmitTicket;
use App\Livewire\Staff\Tickets\TicketDetail;
use App\Livewire\Staff\Tickets\TicketList;
use App\Livewire\Admin\CategoryManagement;
use App\Livewire\Admin\DepartmentManagement;
use App\Livewire\Admin\SectorManagement;
use App\Livewire\Admin\UserManagement;
use Illuminate\Support\Facades\Route;

// Public ticket status page (linked in email notifications)
Route::get('/ticket/{ticketNumber}', [TicketStatusController::class, 'show'])->name('ticket.status');

// Landing page - redirect to staff login
Route::get('/', fn() => redirect()->route('staff.dashboard'))->name('home');

// Staff routes
Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/submit', StaffSubmitTicket::class)->name('submit');
    Route::get('/settings', Settings::class)->name('settings');
    Route::get('/tickets', TicketList::class)->name('tickets.index');
    Route::get('/tickets/{ticket}', TicketDetail::class)->name('tickets.show');
    Route::get('/attachment/{attachment}', [AttachmentController::class, 'staff'])->name('attachment');

    // Admin only routes
    Route::middleware(['can:users.view'])->group(function () {
        Route::get('/users', UserManagement::class)->name('users.index');
        Route::get('/sectors', SectorManagement::class)->name('sectors.index');
        Route::get('/departments', DepartmentManagement::class)->name('departments.index');
        Route::get('/categories', CategoryManagement::class)->name('categories.index');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
