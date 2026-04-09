<?php

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    public $users;
    public $roles;
    public $name, $email, $password, $selected_roles = [];
    public $userId;
    public $isEdit = false;
    public $showModal = false;

    public function mount()
    {
        if (!auth()->user()->can('manage users')) {
            session()->flash('error', 'Anda tidak memiliki akses ke manajemen user.');
            return $this->redirect(route('dashboard'), navigate: true);
        }
        $this->users = User::with('roles')->get();
        $this->roles = Role::all();
    }

    public function resetFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->selected_roles = [];
        $this->isEdit = false;
    }

    public function openModal()
    {
        $this->authorize('manage users');
        $this->resetFields();
        $this->showModal = true;
    }

    public function save()
    {
        $this->authorize('manage users');
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'selected_roles' => 'required|array'
        ];

        if (!$this->isEdit) {
            $rules['password'] = 'required|min:6';
        }

        $this->validate($rules);

        if ($this->isEdit) {
            $user = User::find($this->userId);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);
            if ($this->password) {
                $user->update(['password' => Hash::make($this->password)]);
            }
            $user->syncRoles($this->selected_roles);
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $user->assignRole($this->selected_roles);
        }

        $this->showModal = false;
        $this->users = User::with('roles')->get();
        $this->dispatch('notify', 'User berhasil disimpan');
    }

    public function edit($id)
    {
        $this->authorize('manage users');
        $user = User::find($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selected_roles = $user->roles->pluck('name')->toArray();
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        $this->authorize('manage users');
        User::destroy($id);
        $this->users = User::with('roles')->get();
        $this->dispatch('notify', 'User berhasil dihapus');
    }
};
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-slate-800">Daftar Pengguna</h3>
        <div class="flex items-center space-x-3">
            @can('view trash')
            <a href="{{ route('trash.user.index') }}" class="text-slate-500 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-all" title="Buka Trash">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </a>
            @endcan
            @can('manage users')
            <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah User
            </button>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Nama</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Email</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Role</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $user)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-medium text-slate-900">{{ $user->name }}</td>
                    <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        @foreach($user->roles as $role)
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full mr-1">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        @can('manage users')
                        <button wire:click="edit({{ $user->id }})" class="text-amber-600 hover:text-amber-700 font-medium">Edit</button>
                        <button wire:click="delete({{ $user->id }})" wire:confirm="Yakin ingin menghapus user ini?" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
                        @else
                        <span class="text-xs text-slate-400 italic">No Access</span>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal User -->
    @if($showModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h4 class="text-lg font-bold text-slate-800">{{ $isEdit ? 'Edit User' : 'Tambah User' }}</h4>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama</label>
                    <input wire:model="name" type="text" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input wire:model="email" type="email" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password {{ $isEdit ? '(Kosongkan jika tidak diubah)' : '' }}</label>
                    <input wire:model="password" type="password" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500">
                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Roles</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($roles as $role)
                        <label class="flex items-center space-x-2">
                            <input wire:model="selected_roles" type="checkbox" value="{{ $role->name }}" class="rounded text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-slate-600">{{ $role->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('selected_roles') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 flex justify-end space-x-3">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                <button wire:click="save" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold transition-colors">Simpan</button>
            </div>
        </div>
    </div>
    @endif
</div>
