<?php

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    public $search = '';
    public $roles;
    public $name, $email, $password, $selected_roles = [];
    public $userId;
    public $isEdit = false;
    public $showModal = false;
    public $showPassword = false;

    public function mount()
    {
        if (!Gate::allows('manage users') && !auth()->user()->hasRole('admin')) {
            session()->flash('error', 'Anda tidak memiliki hak akses ke manajemen user.');
            return $this->redirect(route('dashboard'), navigate: true);
        }
        $this->roles = Role::all();
    }

    public function getUsersProperty()
    {
        return User::with('roles')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            }))
            ->get();
    }

    public function getStatsProperty()
    {
        $users = User::with('roles')->get();

        return [
            'total' => $users->count(),
            'active' => $users->where('is_active', true)->count(),
            'inactive' => $users->where('is_active', false)->count(),
            'top_role' => Role::withCount('users')->orderByDesc('users_count')->first(),
        ];
    }

    public function roleColor($roleName)
    {
        $map = [
            'admin' => ['bg-rose-50', 'text-rose-700', 'border-rose-200'],
            'staff' => ['bg-sky-50', 'text-sky-700', 'border-sky-200'],
            'owner' => ['bg-violet-50', 'text-violet-700', 'border-violet-200'],
        ];
        if (isset($map[$roleName])) {
            return $map[$roleName];
        }
        $palette = [
            ['bg-emerald-50', 'text-emerald-700', 'border-emerald-200'],
            ['bg-violet-50', 'text-violet-700', 'border-violet-200'],
            ['bg-amber-50', 'text-amber-700', 'border-amber-200'],
            ['bg-teal-50', 'text-teal-700', 'border-teal-200'],
            ['bg-orange-50', 'text-orange-700', 'border-orange-200'],
            ['bg-cyan-50', 'text-cyan-700', 'border-cyan-200'],
        ];
        return $palette[crc32($roleName) % count($palette)];
    }

    public function initials($name)
    {
        $parts = preg_split('/\s+/', trim($name));
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper(mb_substr($part, 0, 1));
        }
        return $initials ?: '?';
    }

    public function toggleActive($id)
    {
        if (!Gate::allows('manage users') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk mengubah status user.');
            return;
        }

        if ($id === auth()->id()) {
            $this->dispatch('notify', 'Anda tidak bisa menonaktifkan akun sendiri!');
            return;
        }

        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        $this->dispatch('notify', 'Status user berhasil diubah');
    }

    public function resetFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->selected_roles = [];
        $this->isEdit = false;
        $this->showPassword = false;
    }

    public function openModal()
    {
        if (!Gate::allows('manage users') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menambah user.');
            return;
        }
        $this->resetFields();
        $this->showModal = true;
    }

    public function save()
    {
        if (!Gate::allows('manage users') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menyimpan data ini.');
            return;
        }
        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'selected_roles' => 'required|array'
        ];

        if (!$this->isEdit) {
            $rules['password'] = 'required|min:6';
        } elseif ($this->password) {
            $rules['password'] = 'min:6';
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
        $this->dispatch('notify', 'User berhasil disimpan');
    }

    public function edit($id)
    {
        if (!Gate::allows('manage users') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk mengedit user.');
            return;
        }
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
        if (!Gate::allows('manage users') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menghapus user.');
            return;
        }

        if ($id === auth()->id()) {
            $this->dispatch('notify', 'Anda tidak bisa menghapus akun sendiri!');
            return;
        }

        User::destroy($id);
        $this->dispatch('notify', 'User berhasil dihapus');
    }
};
?>

<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Daftar Pengguna</h3>
            <p class="text-sm text-slate-500 mt-0.5">Kelola pengguna dan role yang dimiliki</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all" placeholder="Cari nama atau email...">
            </div>
            @can('view trash')
            <a href="{{ route('trash.user.index') }}" class="text-slate-400 hover:text-red-600 hover:bg-red-50 p-2.5 rounded-lg border border-slate-200 transition-all" title="Buka Trash">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </a>
            @endcan
            @can('manage users')
            <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center whitespace-nowrap">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah User
            </button>
            @endcan
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-800">{{ $this->stats['total'] }}</p>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total User</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-800">{{ $this->stats['active'] }}</p>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">User Aktif</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-800">{{ $this->stats['inactive'] }}</p>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">User Nonaktif</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <p class="text-sm font-black text-slate-800 truncate">{{ $this->stats['top_role']?->name ?? '-' }}</p>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Role Terbanyak</p>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-800">Daftar Pengguna</h4>
                <p class="text-xs text-slate-500 mt-0.5">{{ $this->users->count() }} user ditemukan</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengguna</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($this->users as $user)
                    <tr class="hover:bg-slate-50 transition-colors {{ !$user->is_active ? 'bg-slate-50/60' : '' }}">
                        <td class="px-6 py-4">
                            @if($user->id === auth()->id())
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold uppercase tracking-wide rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                        Aktif
                                    </span>
                                </div>
                            @else
                                <button wire:click="toggleActive({{ $user->id }})" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $user->is_active ? 'bg-emerald-500 focus:ring-emerald-500' : 'bg-slate-200 focus:ring-slate-400' }}" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $user->name }}">
                                    <span class="sr-only">Toggle Active</span>
                                    <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $user->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $user->is_active ? 'from-blue-500 to-indigo-600' : 'from-slate-400 to-slate-500' }} flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                        {{ $this->initials($user->name) }}
                                    </div>
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 flex items-center gap-1.5 {{ !$user->is_active ? 'text-slate-400' : '' }}">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="text-[10px] bg-blue-50 text-blue-600 border border-blue-200 px-1.5 py-0.5 rounded-full font-bold">Anda</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        {{ $user->email }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($user->roles as $role)
                                    @php [$bg, $txt, $bd] = $this->roleColor($role->name); @endphp
                                    <span class="px-2 py-1 {{ $bg }} {{ $txt }} border {{ $bd }} text-[10px] font-bold rounded-full capitalize">{{ $role->name }}</span>
                                @empty
                                    <span class="px-2 py-1 bg-slate-100 text-slate-500 border border-slate-200 text-[10px] font-bold rounded-full">Tanpa Role</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            @can('manage users')
                            <button wire:click="edit({{ $user->id }})" class="text-amber-600 hover:text-amber-700 font-medium p-1.5 rounded-lg hover:bg-amber-50 transition-colors" title="Edit">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            @if($user->id !== auth()->id())
                            <button wire:click="delete({{ $user->id }})" wire:confirm="Yakin ingin menghapus user ini?" class="text-red-600 hover:text-red-700 font-medium p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Hapus">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @else
                            <span class="text-[10px] text-slate-300 font-semibold uppercase" title="Akun sendiri tidak dapat dihapus">Terlindungi</span>
                            @endif
                            @else
                            <span class="text-xs text-slate-400 italic">No Access</span>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <p class="text-slate-400 italic text-sm">@if($this->search) User tidak ditemukan untuk pencarian "{{ $this->search }}". @else Belum ada user. @endif</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal User -->
    @if($showModal)
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg flex flex-col max-h-[90vh] overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <div>
                    <h4 class="text-lg font-bold text-slate-800">{{ $isEdit ? 'Edit User' : 'Tambah User' }}</h4>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $isEdit ? 'Ubah data dan role user' : 'Buat akun baru dan tentukan role' }}</p>
                </div>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4 overflow-y-auto custom-scrollbar">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nama Lengkap</label>
                    <input wire:model="name" type="text" placeholder="contoh: Budi Santoso" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('name') border-red-500 @enderror">
                    @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Email</label>
                    <input wire:model="email" type="email" placeholder="nama@tokokeluarga.com" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('email') border-red-500 @enderror">
                    @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Password</label>
                    <div class="relative">
                        <input wire:model="password" type="{{ $showPassword ? 'text' : 'password' }}" placeholder="{{ $isEdit ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' }}" class="w-full px-4 py-2.5 pr-12 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('password') border-red-500 @enderror">
                        <button type="button" wire:click="$toggle('showPassword')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            @if($showPassword)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            @endif
                        </button>
                    </div>
                    @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-bold text-slate-700">Roles</label>
                        <span class="text-xs font-bold {{ count($selected_roles) > 0 ? 'text-emerald-600' : 'text-slate-400' }}">{{ count($selected_roles) }} dipilih</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @forelse($roles as $role)
                            @php [$bg, $txt, $bd] = $this->roleColor($role->name); @endphp
                            <label class="flex items-center space-x-3 p-3 rounded-xl border cursor-pointer transition-all {{ in_array($role->name, $selected_roles) ? 'bg-blue-50 border-blue-300' : 'bg-slate-50 border-slate-200 hover:border-slate-300' }}">
                                <input wire:model="selected_roles" type="checkbox" value="{{ $role->name }}" class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 transition-colors">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $bg }} {{ $txt }} border {{ $bd }}"></span>
                                    <span class="text-sm font-medium text-slate-700 capitalize">{{ $role->name }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-slate-400 italic col-span-2">Belum ada role. Buat role terlebih dahulu.</p>
                        @endforelse
                    </div>
                    @error('selected_roles') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                <p class="text-xs text-slate-400">Role menentukan hak akses user</p>
                <div class="flex space-x-3">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium transition-colors">Batal</button>
                    <button wire:click="save" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 transition-all active:scale-95">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Buat User' }}
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
