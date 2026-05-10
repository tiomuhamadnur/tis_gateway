<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserManagement extends Component
{
    use WithPagination;

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editingUserId = null;
    public string $search = '';

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $selectedRoles = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function createUser(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'selectedRoles' => 'array',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $user->syncRoles($this->selectedRoles);

        $this->resetForm();
        $this->showCreateModal = false;
        session()->flash('success', 'User berhasil dibuat.');
    }

    public function editUser(int $userId): void
    {
        $user = User::with('roles')->findOrFail($userId);
        $this->editingUserId = $userId;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->showEditModal = true;
    }

    public function updateUser(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editingUserId)],
            'selectedRoles' => 'array',
        ]);

        if ($this->password) {
            $this->validate(['password' => 'min:8|confirmed']);
        }

        $user = User::findOrFail($this->editingUserId);
        $data = ['name' => $this->name, 'email' => $this->email];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);
        $user->syncRoles($this->selectedRoles);

        $this->resetForm();
        $this->showEditModal = false;
        session()->flash('success', 'User berhasil diupdate.');
    }

    public function deleteUser(int $userId): void
    {
        if ($userId === auth()->id()) {
            session()->flash('error', 'Tidak dapat menghapus akun sendiri.');
            return;
        }

        User::findOrFail($userId)->delete();
        session()->flash('success', 'User berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = [];
        $this->editingUserId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        $roles = Role::orderBy('name')->get();

        return view('cms.user-management', compact('users', 'roles'));
    }
}
