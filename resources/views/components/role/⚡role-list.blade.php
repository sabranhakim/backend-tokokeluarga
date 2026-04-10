<?php

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

new class extends Component {
    public $roles;
    public $permissions;
    public $name;
    public $selected_permissions = [];
    public $roleId;
    public $isEdit = false;
    public $showModal = false;

    public function mount()
    {
        if (!auth()->user()->can('manage roles')) {
            session()->flash('error', 'Anda tidak memiliki akses ke manajemen role.');
            return $this->redirect(route('dashboard'), navigate: true);
        }
        
        $this->roles = Role::with('permissions')->get();
        $this->permissions = Permission::all();
    }

    public function resetFields()
    {
        $this->name = '';
        $this->selected_permissions = [];
        $this->isEdit = false;
    }

    public function openModal()
    {
        $this->authorize('manage roles');
        $this->resetFields();
        $this->showModal = true;
    }

    public function save()
    {
        $this->authorize('manage roles');
        $this->validate([
            'name' => 'required|unique:roles,name,' . $this->roleId,
        ]);

        if ($this->isEdit) {
            $role = Role::find($this->roleId);
            $role->update(['name' => $this->name]);
            $role->syncPermissions($this->selected_permissions);
        } else {
            $role = Role::create(['name' => $this->name]);
            $role->givePermissionTo($this->selected_permissions);
        }

        $this->showModal = false;
        $this->roles = Role::with('permissions')->get(); // Update data without full mount
        $this->dispatch('notify', 'Role berhasil disimpan');
    }

    public function edit($id)
    {
        $this->authorize('manage roles');
        $role = Role::find($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->selected_permissions = $role->permissions->pluck('name')->toArray();
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        $this->authorize('manage roles');
        Role::destroy($id);
        $this->roles = Role::with('permissions')->get(); // Update data without full mount
        $this->dispatch('notify', 'Role berhasil dihapus');
    }
};
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-slate-800">Daftar Role</h3>
        @can('manage roles')
        <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Tambah Role
        </button>
        @endcan
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Role</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Permission</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($roles as $role)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-medium text-slate-900">{{ $role->name }}</td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach($role->permissions as $perm)
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-xs rounded-full">{{ $perm->name }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        @can('manage roles')
                        <button wire:click="edit({{ $role->id }})" class="text-amber-600 hover:text-amber-700 font-medium" title="Edit">
                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button wire:click="delete({{ $role->id }})" wire:confirm="Yakin ingin menghapus role ini?" class="text-red-600 hover:text-red-700 font-medium" title="Hapus">
                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        @else
                        <span class="text-xs text-slate-400 italic">No Access</span>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal Role -->
    @if($showModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h4 class="text-lg font-bold text-slate-800">{{ $isEdit ? 'Edit Role' : 'Tambah Role' }}</h4>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Role</label>
                    <input wire:model="name" type="text" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Permissions</label>
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-xl max-h-60 overflow-y-auto">
                        @foreach($permissions as $perm)
                        <label class="flex items-center space-x-2 p-1 hover:bg-white rounded transition-colors cursor-pointer">
                            <input wire:model="selected_permissions" type="checkbox" value="{{ $perm->name }}" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                            <span class="text-sm text-slate-600">{{ $perm->name }}</span>
                        </label>
                        @endforeach
                    </div>
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
