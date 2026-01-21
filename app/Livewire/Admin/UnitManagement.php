<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Unit Management - Sistem Aduan CLAB')]
class UnitManagement extends Component
{
    public bool $showModal = false;
    public ?Unit $editingUnit = null;

    #[Validate('nullable|exists:departments,id')]
    public ?int $department_id = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('array')]
    public array $emails = [];

    #[Validate('nullable|email|max:255')]
    public string $newEmail = '';

    public bool $is_active = true;

    public function openModal(?Unit $unit = null): void
    {
        $this->resetValidation();

        if ($unit && $unit->exists) {
            $this->editingUnit = $unit;
            $this->department_id = $unit->department_id;
            $this->name = $unit->name;
            $this->description = $unit->description ?? '';
            $this->emails = $unit->emails ?? [];
            $this->is_active = $unit->is_active;
        } else {
            $this->editingUnit = null;
            $this->department_id = null;
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
        $this->editingUnit = null;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'department_id' => $this->department_id,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'emails' => !empty($this->emails) ? $this->emails : null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingUnit) {
            $this->editingUnit->update($data);
        } else {
            Unit::create($data);
        }

        $this->closeModal();
    }

    public function delete(Unit $unit): void
    {
        $unit->delete();
    }

    public function render()
    {
        return view('livewire.admin.unit-management', [
            'units' => Unit::with('department')->withCount('categories')->orderBy('name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
        ]);
    }
}
