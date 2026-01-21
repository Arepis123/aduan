<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\Sector;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Department Management - Sistem Aduan CLAB')]
class DepartmentManagement extends Component
{
    public bool $showModal = false;
    public ?Department $editingDepartment = null;

    #[Validate('nullable|exists:sectors,id')]
    public ?int $sector_id = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('array')]
    public array $emails = [];

    #[Validate('nullable|email|max:255')]
    public string $newEmail = '';

    public bool $is_active = true;

    public function openModal(?Department $department = null): void
    {
        $this->resetValidation();

        if ($department && $department->exists) {
            $this->editingDepartment = $department;
            $this->sector_id = $department->sector_id;
            $this->name = $department->name;
            $this->description = $department->description ?? '';
            $this->emails = $department->emails ?? [];
            $this->is_active = $department->is_active;
        } else {
            $this->editingDepartment = null;
            $this->sector_id = null;
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
        $this->editingDepartment = null;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'sector_id' => $this->sector_id,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'emails' => !empty($this->emails) ? $this->emails : null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingDepartment) {
            $this->editingDepartment->update($data);
        } else {
            Department::create($data);
        }

        $this->closeModal();
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }

    public function render()
    {
        return view('livewire.admin.department-management', [
            'departments' => Department::with('sector')->withCount(['users', 'tickets', 'categories'])->orderBy('name')->get(),
            'sectors' => Sector::active()->orderBy('name')->get(),
        ]);
    }
}
