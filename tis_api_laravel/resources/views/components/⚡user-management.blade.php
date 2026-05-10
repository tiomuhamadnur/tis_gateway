<?php

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingUser = null;

    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $selectedRoles = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
        'selectedRoles' => 'array',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function createUser()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $user->syncRoles($this->selectedRoles);

        $this->resetForm();
        $this->showCreateModal = false;
        session()->flash('message', 'User created successfully.');
    }

    public function editUser($userId)
    {
        $this->editingUser = User::find($userId);
        $this->name = $this->editingUser->name;
        $this->email = $this->editingUser->email;
        $this->selectedRoles = $this->editingUser->roles->pluck('name')->toArray();
        $this->showEditModal = true;
    }

    public function updateUser()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->editingUser->id,
            'selectedRoles' => 'array',
        ]);

        $this->editingUser->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        if ($this->password) {
            $this->validate(['password' => 'min:8|confirmed']);
            $this->editingUser->update(['password' => Hash::make($this->password)]);
        }

        $this->editingUser->syncRoles($this->selectedRoles);

        $this->resetForm();
        $this->showEditModal = false;
        session()->flash('message', 'User updated successfully.');
    }

    public function deleteUser($userId)
    {
        User::find($userId)->delete();
        session()->flash('message', 'User deleted successfully.');
    }

    private function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = [];
        $this->editingUser = null;
    }

    public function render()
    {
        $users = User::with('roles')->paginate(10);
        $roles = Role::all();

        return view('livewire.user-management', compact('users', 'roles'));
    }
};
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">User Management</h1>
        <button wire:click="$set('showCreateModal', true)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Add User
        </button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $user->roles->pluck('name')->join(', ') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button wire:click="editUser({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                        <button wire:click="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $users->links() }}

    <!-- Create User Modal -->
    @if($showCreateModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="create-modal">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create New User</h3>
                <form wire:submit.prevent="createUser">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                        <input wire:model="name" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input wire:model="email" type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input wire:model="password" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                        <input wire:model="password_confirmation" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Roles</label>
                        @foreach($roles as $role)
                        <label class="inline-flex items-center mr-4">
                            <input wire:model="selectedRoles" type="checkbox" value="{{ $role->name }}" class="form-checkbox">
                            <span class="ml-2">{{ $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    <div class="flex justify-end">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="mr-3 px-4 py-2 bg-gray-300 text-gray-900 rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit User Modal -->
    @if($showEditModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="edit-modal">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit User</h3>
                <form wire:submit.prevent="updateUser">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                        <input wire:model="name" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input wire:model="email" type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Password (leave blank to keep current)</label>
                        <input wire:model="password" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                        <input wire:model="password_confirmation" type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Roles</label>
                        @foreach($roles as $role)
                        <label class="inline-flex items-center mr-4">
                            <input wire:model="selectedRoles" type="checkbox" value="{{ $role->name }}" class="form-checkbox">
                            <span class="ml-2">{{ $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    <div class="flex justify-end">
                        <button type="button" wire:click="$set('showEditModal', false)" class="mr-3 px-4 py-2 bg-gray-300 text-gray-900 rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

<div>
    {{-- Waste no more time arguing what a good man should be, be one. - Marcus Aurelius --}}
</div>