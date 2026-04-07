<?php

use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component {
    public $roles;
    public $name;
    public $roleId;
    public $isEdit = false;
    public $showModal = false;

    public function mount()
    {
        $this->roles = Role::all();
    }

    public function resetFields()
    {
        $this->name = '';
        $this->isEdit = false;
    }

    public function openModal()
    {
        $this->resetFields();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|unique:roles,name,' . $this->roleId,
        ]);

        if ($this->isEdit) {
            Role::find($this->roleId)->update(['name' => $this->name]);
        } else {
            Role::create(['name' => $this->name]);
        }

        $this->showModal = false;
        $this->mount();
        $this->dispatch('notify', 'Role berhasil disimpan');
    }

    public function edit($id)
    {
        $role = Role::find($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        Role::destroy($id);
        $this->mount();
        $this->dispatch('notify', 'Role berhasil dihapus');
    }
};
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-slate-800">Daftar Role</h3>
        <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Tambah Role
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden max-w-2xl">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase">Nama Role</th>
                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($roles as $role)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-medium text-slate-900">{{ $role->name }}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button wire:click="edit({{ $role->id }})" class="text-amber-600 hover:text-amber-700 font-medium">Edit</button>
                        <button wire:click="delete({{ $role->id }})" wire:confirm="Yakin ingin menghapus role ini?" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal Role -->
    @if($showModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h4 class="text-lg font-bold text-slate-800">{{ $isEdit ? 'Edit Role' : 'Tambah Role' }}</h4>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Role</label>
                    <input wire:model="name" type="text" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
