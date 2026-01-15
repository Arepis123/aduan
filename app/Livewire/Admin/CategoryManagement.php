<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Department;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Category Management - Sistem Aduan')]
class CategoryManagement extends Component
{
    public bool $showModal = false;
    public ?Category $editingCategory = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('nullable|exists:departments,id')]
    public ?int $department_id = null;

    public bool $is_active = true;

    public function openModal(?Category $category = null): void
    {
        $this->resetValidation();

        if ($category && $category->exists) {
            $this->editingCategory = $category;
            $this->name = $category->name;
            $this->description = $category->description ?? '';
            $this->department_id = $category->department_id;
            $this->is_active = $category->is_active;
        } else {
            $this->editingCategory = null;
            $this->name = '';
            $this->description = '';
            $this->department_id = null;
            $this->is_active = true;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingCategory = null;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'department_id' => $this->department_id,
            'is_active' => $this->is_active,
        ];

        if ($this->editingCategory) {
            $this->editingCategory->update($data);
        } else {
            Category::create($data);
        }

        $this->closeModal();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function render()
    {
        return view('livewire.admin.category-management', [
            'categories' => Category::with('department')->withCount('tickets')->orderBy('name')->get(),
            'departments' => Department::active()->get(),
        ]);
    }
}
