<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">User Management</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Kelola akun pengguna dan role akses</p>
        </div>
        @can('manage users')
        <button wire:click="openCreateModal" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah User
        </button>
        @endcan
    </div>

    @if(session()->has('success'))
    <div class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
        <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session()->has('error'))
    <div class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
        <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        {{ session('error') }}
    </div>
    @endif

    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
            <div class="relative flex-1 max-w-sm">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..." class="w-full rounded-lg border border-zinc-200 bg-zinc-50 py-2 pl-9 pr-4 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100 dark:placeholder-zinc-400">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Nama</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Roles</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Dibuat</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @forelse($users as $user)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $user->email }}</td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->roles as $role)
                                <span @class(['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' => $role->name === 'superadmin', 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' => $role->name !== 'superadmin'])>
                                    {{ $role->name }}
                                </span>
                                @empty
                                <span class="text-xs text-zinc-400">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-zinc-500">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-right text-sm">
                            @can('manage users')
                            <button wire:click="editUser({{ $user->id }})" class="mr-2 inline-flex items-center rounded-md border border-zinc-200 bg-white px-2.5 py-1.5 text-xs font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200">
                                Edit
                            </button>
                            @if($user->id !== auth()->id())
                            <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Yakin hapus user '{{ $user->name }}'?" class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 shadow-sm hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                                Hapus
                            </button>
                            @endif
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-zinc-400">
                            @if($search)
                            Tidak ada user yang cocok dengan "{{ $search }}"
                            @else
                            Belum ada user.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Create Modal --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Tambah User Baru</h3>
                <button wire:click="$set('showCreateModal', false)" class="text-zinc-400 hover:text-zinc-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="createUser" class="space-y-4 px-6 py-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nama</label>
                    <input wire:model="name" type="text" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                    <input wire:model="email" type="email" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Password</label>
                    <input wire:model="password" type="password" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Konfirmasi Password</label>
                    <input wire:model="password_confirmation" type="password" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Roles</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach($roles as $role)
                        <label class="flex cursor-pointer items-center gap-2">
                            <input wire:model="selectedRoles" type="checkbox" value="{{ $role->name }}" class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ ucfirst($role->name) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300">Batal</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Edit Modal --}}
    @if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Edit User</h3>
                <button wire:click="$set('showEditModal', false)" class="text-zinc-400 hover:text-zinc-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="updateUser" class="space-y-4 px-6 py-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nama</label>
                    <input wire:model="name" type="text" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                    <input wire:model="email" type="email" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Password Baru (kosongkan jika tidak diubah)</label>
                    <input wire:model="password" type="password" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Konfirmasi Password Baru</label>
                    <input wire:model="password_confirmation" type="password" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Roles</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach($roles as $role)
                        <label class="flex cursor-pointer items-center gap-2">
                            <input wire:model="selectedRoles" type="checkbox" value="{{ $role->name }}" class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ ucfirst($role->name) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showEditModal', false)" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300">Batal</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
