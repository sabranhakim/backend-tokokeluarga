<?php

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    public $search = '';
    public $name;
    public $selected_permissions = [];
    public $roleId;
    public $isEdit = false;
    public $showModal = false;

    public function mount()
    {
        if (!Gate::allows('manage roles') && !auth()->user()->hasRole('admin')) {
            session()->flash('error', 'Anda tidak memiliki hak akses ke manajemen role.');
            return $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function getRolesProperty()
    {
        return Role::with(['permissions', 'users'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->get();
    }

    public function getPermissionsProperty()
    {
        return Permission::all();
    }

    public function getGroupedPermissionsProperty()
    {
        $groups = [
            'Umum' => ['view dashboard', 'manage activity'],
            'User & Role' => ['manage users', 'manage roles'],
            'Master Barang' => ['view barang', 'manage barang', 'view kategori', 'manage kategori'],
            'Supplier' => ['view supplier', 'manage supplier'],
            'Penerimaan' => ['view penerimaan', 'create penerimaan', 'verify penerimaan', 'delete penerimaan'],
            'Barang Keluar' => ['view barang_keluar', 'create barang_keluar', 'delete barang_keluar'],
            'Retur Pembelian' => ['view retur_pembelian', 'create retur_pembelian', 'delete retur_pembelian'],
            'Retur Penjualan' => ['view retur_penjualan', 'create retur_penjualan', 'delete retur_penjualan'],
            'Stock Opname' => ['view stock_opname', 'create stock_opname', 'finalize stock_opname', 'delete stock_opname'],
            'Akses Mobile' => ['view barang', 'view supplier', 'view kategori', 'view penerimaan', 'create penerimaan', 'create barang_keluar', 'view barang_keluar'],
            'Laporan' => ['manage laporan'],
            'Trash' => ['view trash', 'manage trash'],
        ];

        $grouped = [];
        $assigned = [];

        foreach ($groups as $label => $perms) {
            $matches = $this->permissions->whereIn('name', $perms);
            if ($matches->count() > 0) {
                $grouped[$label] = $matches;
            }
            $assigned = array_merge($assigned, $perms);
        }

        // Catch-all for any permissions not in the groups
        $others = $this->permissions->whereNotIn('name', $assigned);
        if ($others->count() > 0) {
            $grouped['Lainnya'] = $others;
        }

        return $grouped;
    }

    public function getGroupColorsProperty()
    {
        $colors = [
            'Umum' => ['bg-slate-100', 'text-slate-700', 'border-slate-200'],
            'User & Role' => ['bg-indigo-50', 'text-indigo-700', 'border-indigo-200'],
            'Master Barang' => ['bg-sky-50', 'text-sky-700', 'border-sky-200'],
            'Supplier' => ['bg-teal-50', 'text-teal-700', 'border-teal-200'],
            'Penerimaan' => ['bg-emerald-50', 'text-emerald-700', 'border-emerald-200'],
            'Barang Keluar' => ['bg-orange-50', 'text-orange-700', 'border-orange-200'],
            'Retur Pembelian' => ['bg-rose-50', 'text-rose-700', 'border-rose-200'],
            'Retur Penjualan' => ['bg-pink-50', 'text-pink-700', 'border-pink-200'],
            'Stock Opname' => ['bg-violet-50', 'text-violet-700', 'border-violet-200'],
            'Akses Mobile' => ['bg-cyan-50', 'text-cyan-700', 'border-cyan-200'],
            'Laporan' => ['bg-amber-50', 'text-amber-700', 'border-amber-200'],
            'Trash' => ['bg-red-50', 'text-red-700', 'border-red-200'],
            'Lainnya' => ['bg-slate-100', 'text-slate-700', 'border-slate-200'],
        ];
        return $colors;
    }

    public function getGroupColors($group)
    {
        return $this->groupColors[$group] ?? ['bg-slate-100', 'text-slate-700', 'border-slate-200'];
    }

    public function permissionGroupOf($permissionName)
    {
        foreach ($this->groupedPermissions as $group => $perms) {
            if ($perms->contains('name', $permissionName)) {
                return $group;
            }
        }
        return 'Lainnya';
    }

    public function updatingSearch()
    {
        // no pagination, nothing to reset
    }

    public function selectGroup($group)
    {
        $names = $this->groupedPermissions[$group]->pluck('name')->toArray();
        $this->selected_permissions = array_values(array_unique(array_merge($this->selected_permissions, $names)));
    }

    public function clearGroup($group)
    {
        $names = $this->groupedPermissions[$group]->pluck('name')->toArray();
        $this->selected_permissions = array_values(array_diff($this->selected_permissions, $names));
    }

    public function selectAll()
    {
        $this->selected_permissions = $this->permissions->pluck('name')->all();
    }

    public function clearAll()
    {
        $this->selected_permissions = [];
    }

    public function resetFields()
    {
        $this->name = '';
        $this->selected_permissions = [];
        $this->isEdit = false;
    }

    public function openModal()
    {
        if (!Gate::allows('manage roles') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menambah role.');
            return;
        }
        $this->resetFields();
        $this->showModal = true;
    }

    public function save()
    {
        if (!Gate::allows('manage roles') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menyimpan data ini.');
            return;
        }
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
        $this->dispatch('notify', 'Role berhasil disimpan');
    }

    public function edit($id)
    {
        if (!Gate::allows('manage roles') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk mengedit role.');
            return;
        }
        $role = Role::find($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->selected_permissions = $role->permissions->pluck('name')->toArray();
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        if (!Gate::allows('manage roles') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menghapus role.');
            return;
        }

        $role = Role::withCount('users')->find($id);
        if ($role && $role->users_count > 0) {
            $this->dispatch('notify', 'Role tidak dapat dihapus karena masih digunakan oleh ' . $role->users_count . ' user.');
            return;
        }
        if ($role && $role->name === 'admin') {
            $this->dispatch('notify', 'Role admin tidak dapat dihapus.');
            return;
        }

        Role::destroy($id);
        $this->dispatch('notify', 'Role berhasil dihapus');
    }

    public function getStatsProperty()
    {
        $roles = Role::withCount(['users', 'permissions'])->get();
        $totalUsers = $roles->sum('users_count');

        return [
            'total_roles' => $roles->count(),
            'total_permissions' => $this->permissions->count(),
            'total_users' => $totalUsers,
            'top_role' => $roles->sortByDesc('users_count')->first(),
        ];
    }
};
?>

<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Manajemen Role</h3>
            <p class="text-sm text-slate-500 mt-0.5">Kelola role dan hak akses pengguna sistem</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all" placeholder="Cari role...">
            </div>
            @can('manage roles')
            <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center whitespace-nowrap">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Role
            </button>
            @endcan
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-800">{{ $this->stats['total_roles'] }}</p>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Role</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-800">{{ $this->stats['total_permissions'] }}</p>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Permission</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-800">{{ $this->stats['total_users'] }}</p>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total User</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <p class="text-sm font-black text-slate-800 truncate">{{ $this->stats['top_role']?->name ?? '-' }}</p>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Role Terbanyak User</p>
            </div>
        </div>
    </div>

    <!-- Roles Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-800">Daftar Role</h4>
                <p class="text-xs text-slate-500 mt-0.5">{{ $this->roles->count() }} role ditemukan</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Role</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Hak Akses</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Jumlah Permission</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Pengguna</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($this->roles as $role)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $role->name === 'admin' ? 'from-rose-500 to-red-600' : 'from-blue-500 to-indigo-600' }} flex items-center justify-center text-white font-bold shadow-sm">
                                    {{ strtoupper(substr($role->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 capitalize">{{ $role->name }}</p>
                                    @if($role->name === 'admin')
                                        <span class="text-[10px] text-rose-600 font-bold uppercase tracking-wide">Akses Penuh</span>
                                    @else
                                        <span class="text-[10px] text-slate-400 font-medium">{{ $role->users_count }} pengguna memiliki role ini</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5 max-w-md">
                                @foreach($role->permissions->take(6) as $perm)
                                    @php
                                        $group = $this->permissionGroupOf($perm->name);
                                        [$bg, $txt, $bd] = $this->getGroupColors($group);
                                    @endphp
                                    <span class="px-2 py-0.5 {{ $bg }} {{ $txt }} border {{ $bd }} text-[10px] font-semibold rounded-full">{{ $perm->name }}</span>
                                @endforeach
                                @if($role->permissions->count() > 6)
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-semibold rounded-full">+{{ $role->permissions->count() - 6 }} lainnya</span>
                                @endif
                                @if($role->permissions->isEmpty())
                                    <span class="text-xs text-slate-400 italic">Belum ada permission</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-lg">{{ $role->permissions->count() }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg">{{ $role->users_count }}</span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            @can('manage roles')
                            <button wire:click="edit({{ $role->id }})" class="text-amber-600 hover:text-amber-700 font-medium" title="Edit">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            @if($role->name !== 'admin')
                            <button wire:click="delete({{ $role->id }})" wire:confirm="Yakin ingin menghapus role ini?" class="text-red-600 hover:text-red-700 font-medium" title="Hapus">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @else
                            <span class="text-[10px] text-slate-300 font-semibold uppercase" title="Role admin tidak dapat dihapus">Terlindungi</span>
                            @endif
                            @else
                            <span class="text-xs text-slate-400 italic">No Access</span>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center">
                            <p class="text-slate-400 italic text-sm">@if($search) Role tidak ditemukan untuk pencarian "{{ $search }}". @else Belum ada role. @endif</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Role -->
    @if($showModal)
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl flex flex-col max-h-[90vh] overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <div>
                    <h4 class="text-lg font-bold text-slate-800">{{ $isEdit ? 'Edit Role' : 'Tambah Role' }}</h4>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $isEdit ? 'Ubah nama dan hak akses role' : 'Buat role baru dan tentukan hak aksesnya' }}</p>
                </div>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nama Role</label>
                    <input wire:model="name" type="text" placeholder="contoh: kasir, admin_gudang" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('name') border-red-500 @enderror">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <label class="block text-sm font-bold text-slate-700">Permissions</label>
                            <p class="text-xs text-slate-500">{{ count($selected_permissions) }} dari {{ $this->permissions->count() }} permission dipilih</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="selectAll" type="button" class="text-xs font-bold text-blue-600 hover:text-blue-700 px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors">Pilih Semua</button>
                            <span class="text-slate-200">|</span>
                            <button wire:click="clearAll" type="button" class="text-xs font-bold text-slate-500 hover:text-slate-700 px-2 py-1 rounded-lg hover:bg-slate-50 transition-colors">Reset</button>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @foreach($this->groupedPermissions as $group => $perms)
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                            <div class="flex justify-between items-center mb-3">
                                <div class="flex items-center gap-2">
                                    <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $group }}</h5>
                                    <span class="px-1.5 py-0.5 bg-white text-slate-400 text-[10px] font-bold rounded border border-slate-200">{{ $perms->count() }}</span>
                                </div>
                                @php
                                    $groupSelected = $perms->filter(fn($p) => in_array($p->name, $selected_permissions))->count();
                                    $groupAll = $groupSelected === $perms->count();
                                @endphp
                                @if($groupSelected > 0)
                                    <span class="text-[10px] font-bold text-emerald-600">{{ $groupSelected }}/{{ $perms->count() }} dipilih</span>
                                @endif
                                <button wire:click="{{ $groupAll ? 'clearGroup' : 'selectGroup' }}('{{ $group }}')" type="button" class="text-[10px] font-bold {{ $groupAll ? 'text-slate-400 hover:text-slate-600' : 'text-blue-600 hover:text-blue-700' }} px-2 py-1 rounded-lg hover:bg-white transition-colors">
                                    {{ $groupAll ? 'Bersihkan' : 'Pilih semua' }}
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-1.5">
                                @foreach($perms as $perm)
                                <label class="flex items-center space-x-3 p-2 hover:bg-white rounded-lg transition-all cursor-pointer border border-transparent hover:border-slate-200 group">
                                    <div class="relative flex items-center">
                                        <input wire:model="selected_permissions" type="checkbox" value="{{ $perm->name }}"
                                            class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 transition-colors">
                                    </div>
                                    <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors">{{ $perm->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                <p class="text-xs text-slate-400">{{ count($selected_permissions) }} permission dipilih</p>
                <div class="flex space-x-3">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium transition-colors">Batal</button>
                    <button wire:click="save" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 transition-all active:scale-95">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Buat Role' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</div>
