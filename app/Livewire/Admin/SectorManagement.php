<?php

namespace App\Livewire\Admin;

use App\Models\Sector;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Sector Management - Sistem Aduan CLAB')]
class SectorManagement extends Component
{
    public bool $showModal = false;
    public ?Sector $editingSector = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('array')]
    public array $emails = [];

    #[Validate('nullable|email|max:255')]
    public string $newEmail = '';

    public bool $is_active = true;

    public function openModal(?Sector $sector = null): void
    {
        $this->resetValidation();

        if ($sector && $sector->exists) {
            $this->editingSector = $sector;
            $this->name = $sector->name;
            $this->description = $sector->description ?? '';
            $this->emails = $sector->emails ?? [];
            $this->is_active = $sector->is_active;
        } else {
            $this->editingSector = null;
            $this->name = '';
            $this->description = '';
            $this->emails = [];
            $this->is_active = true;
        }

        $this->newEmail = '';
        $this->showModal = true;
    }

    public function addEmail(): void
    {
        $this->validateOnly('newEmail');

        if ($this->newEmail && !in_array($this->newEmail, $this->emails)) {
            $this->emails[] = $this->newEmail;
            $this->newEmail = '';
        }
    }

    public function removeEmail(int $index): void
    {
        unset($this->emails[$index]);
        $this->emails = array_values($this->emails);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingSector = null;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'emails' => !empty($this->emails) ? $this->emails : null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingSector) {
            $this->editingSector->update($data);
        } else {
            Sector::create($data);
        }

        $this->closeModal();
    }

    public function delete(Sector $sector): void
    {
        $sector->delete();
    }

    public function render()
    {
        return view('livewire.admin.sector-management', [
            'sectors' => Sector::withCount('departments')->orderBy('name')->get(),
        ]);
    }
}
