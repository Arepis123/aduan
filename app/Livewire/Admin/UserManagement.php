<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\Sector;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('User Management - Sistem Aduan CLAB')]
class UserManagement extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public ?User $editingUser = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    public string $password = '';

    #[Validate('required|in:admin,agent')]
    public string $role = 'agent';

    public ?int $sector_id = null;
    public ?int $department_id = null;
    public ?int $unit_id = null;

    #[Validate('nullable|string|max:20')]
    public ?string $phone = '';

    public bool $is_active = true;

    public string $search = '';

    public function openModal(?User $user = null): void
    {
        $this->resetValidation();

        if ($user && $user->exists) {
            $this->editingUser = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->role;
            $this->is_active = $user->is_active;
            $this->sector_id = $user->sector_id;
            $this->department_id = $user->department_id;
            $this->unit_id = $user->unit_id;
            $this->phone = $user->phone ?? '';
            $this->password = '';
        } else {
            $this->editingUser = null;
            $this->name = '';
            $this->email = '';
            $this->password = '';
            $this->role = 'agent';
            $this->is_active = true;
            $this->sector_id = null;
            $this->department_id = null;
            $this->unit_id = null;
            $this->phone = '';
        }

        $this->showModal = true;
    }

    public function updatedSectorId($value): void
    {
        $this->department_id = null;
        $this->unit_id = null;
    }

    public function updatedDepartmentId($value): void
    {
        $this->unit_id = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingUser = null;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email' . ($this->editingUser ? ',' . $this->editingUser->id : ''),
            'role' => 'required|in:admin,agent',
            'sector_id' => 'nullable|exists:sectors,id',
            'department_id' => 'nullable|exists:departments,id',
            'unit_id' => 'nullable|exists:units,id',
            'phone' => 'nullable|string|max:20',
        ];

        if (!$this->editingUser) {
            $rules['password'] = 'required|string|min:8';
        } elseif ($this->password) {
            $rules['password'] = 'string|min:8';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'sector_id' => $this->sector_id,
            'department_id' => $this->department_id,
            'unit_id' => $this->unit_id,
            'phone' => $this->phone ?: null,
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        if ($this->editingUser) {
            $this->editingUser->update($data);
            $this->editingUser->syncRoles([$this->role]);
        } else {
            $data['email_verified_at'] = now();
            $user = User::create($data);
            $user->assignRole($this->role);
        }

        $this->closeModal();
    }

    public function delete(User $user): void
    {
        if ($user->id !== auth()->id()) {
            $user->delete();
        }
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->with(['sector', 'department', 'unit'])
            ->orderBy('name')
            ->paginate(10);

        $sectors = Sector::active()->orderBy('name')->get();
        $departments = $this->sector_id
            ? Department::where('sector_id', $this->sector_id)->active()->orderBy('name')->get()
            : collect();
        $units = $this->department_id
            ? Unit::where('department_id', $this->department_id)->active()->orderBy('name')->get()
            : collect();

        return view('livewire.admin.user-management', [
            'users' => $users,
            'sectors' => $sectors,
            'departments' => $departments,
            'units' => $units,
        ]);
    }
}
