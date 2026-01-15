<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Department Management - Sistem Aduan')]
class DepartmentManagement extends Component
{
    public bool $showModal = false;
    public ?Department $editingDepartment = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('nullable|email|max:255')]
    public string $email = '';

    public bool $is_active = true;

    public function openModal(?Department $department = null): void
    {
        $this->resetValidation();

        if ($department && $department->exists) {
            $this->editingDepartment = $department;
            $this->name = $department->name;
            $this->description = $department->description ?? '';
            $this->email = $department->email ?? '';
            $this->is_active = $department->is_active;
        } else {
            $this->editingDepartment = null;
            $this->name = '';
            $this->description = '';
            $this->email = '';
            $this->is_active = true;
        }

        $this->showModal = true;
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
            'name' => $this->name,
            'description' => $this->description ?: null,
            'email' => $this->email ?: null,
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
            'departments' => Department::withCount(['users', 'tickets', 'categories'])->orderBy('name')->get(),
        ]);
    }
}
