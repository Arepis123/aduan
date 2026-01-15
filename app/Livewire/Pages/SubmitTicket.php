<?php

namespace App\Livewire\Pages;

use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.public')]
#[Title('Submit Ticket - Sistem Aduan')]
class SubmitTicket extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public ?string $phone = '';

    #[Validate('required|exists:departments,id')]
    public ?int $department_id = null;

    #[Validate('required|exists:categories,id')]
    public ?int $category_id = null;

    #[Validate('required|string|max:255')]
    public string $subject = '';

    #[Validate('required|string|min:20')]
    public string $description = '';

    #[Validate('required|in:low,medium,high,urgent')]
    public string $priority = 'medium';

    #[Validate(['attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif'])]
    public array $attachments = [];

    public array $categories = [];
    public ?Ticket $submittedTicket = null;

    public function updatedDepartmentId($value): void
    {
        $this->category_id = null;
        $this->categories = $value
            ? Category::where('department_id', $value)->active()->get()->toArray()
            : [];
    }

    public function submit(): void
    {
        $this->validate();

        $ticket = Ticket::create([
            'requester_name' => $this->name,
            'requester_email' => $this->email,
            'requester_phone' => $this->phone,
            'requester_type' => 'external',
            'department_id' => $this->department_id,
            'category_id' => $this->category_id,
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => 'open',
        ]);

        // Handle file attachments
        foreach ($this->attachments as $attachment) {
            $path = $attachment->store('attachments/' . $ticket->id, 'public');

            TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'filename' => basename($path),
                'original_filename' => $attachment->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $attachment->getMimeType(),
                'size' => $attachment->getSize(),
            ]);
        }

        $this->submittedTicket = $ticket;
        $this->dispatch('ticket-submitted');
    }

    public function removeAttachment($index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function render()
    {
        return view('livewire.pages.submit-ticket', [
            'departments' => Department::active()->get(),
        ]);
    }
}
