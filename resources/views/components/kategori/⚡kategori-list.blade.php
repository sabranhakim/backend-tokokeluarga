<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Kategori;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $isEdit = false;

    // Form fields
    public $kategoriId;
    public $nama_kategori;

    protected $rules = [
        'nama_kategori' => 'required|min:3|unique:kategoris,nama_kategori',
    ];

    public function mount()
    {
        if (!Gate::allows('view kategori') && !auth()->user()->hasAnyRole(['admin', 'staff'])) {
            session()->flash('error', 'Anda tidak memiliki hak akses ke data kategori.');
            return $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function with()
    {
        return [
            'kategoris' => Kategori::withoutGlobalScope('active')
                ->where('nama_kategori', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(20), // Increased pagination for two columns
        ];
    }

    public function toggleActive($id)
    {
        if (!Gate::allows('manage kategori') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk mengubah status kategori.');
            return;
        }
        $kategori = Kategori::withoutGlobalScope('active')->findOrFail($id);
        $kategori->update(['is_active' => !$kategori->is_active]);
        $this->dispatch('notify', 'Status kategori berhasil diubah');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        if (!Gate::allows('manage kategori') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menambah kategori.');
            return;
        }
        $this->resetFields();
        $this->showModal = true;
    }

    public function resetFields()
    {
        $this->kategoriId = null;
        $this->nama_kategori = '';
        $this->isEdit = false;
    }

    public function edit($id)
    {
        if (!Gate::allows('manage kategori') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk mengedit kategori.');
            return;
        }
        $kategori = Kategori::withoutGlobalScope('active')->findOrFail($id);
        $this->kategoriId = $kategori->id;
        $this->nama_kategori = $kategori->nama_kategori;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        if (!Gate::allows('manage kategori') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menyimpan data ini.');
            return;
        }
        $validationRules = $this->rules;
        if ($this->isEdit) {
            $validationRules['nama_kategori'] = 'required|min:3|unique:kategoris,nama_kategori,' . $this->kategoriId;
        }

        $validated = $this->validate($validationRules);

        if ($this->isEdit) {
            Kategori::withoutGlobalScope('active')->find($this->kategoriId)->update($validated);
            $message = 'Kategori berhasil diperbarui';
        } else {
            Kategori::create($validated);
            $message = 'Kategori berhasil ditambahkan';
        }

        $this->showModal = false;
        $this->dispatch('notify', $message);
    }

    public function delete($id)
    {
        if (!Gate::allows('manage kategori') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menghapus kategori.');
            return;
        }
        try {
            Kategori::withoutGlobalScope('active')->destroy($id);
            $this->dispatch('notify', 'Kategori berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Kategori tidak dapat dihapus karena masih digunakan oleh barang.');
        }
    }
};
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Daftar Kategori</h3>
            <p class="text-sm text-slate-500">Kelola kategori untuk pengelompokan barang</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all" placeholder="Cari kategori...">
            </div>
            @can('view trash')
            <a href="{{ route('trash.kategori.index') }}" class="text-slate-500 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-all" title="Buka Trash">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </a>
            @endcan
            @can('manage kategori')
            <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center whitespace-nowrap">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Kategori
            </button>
            @endcan
        </div>
    </div>

    @php
        $chunks = $kategoris->chunk(ceil($kategoris->count() / 2));
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($chunks as $chunk)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider w-16 text-center">Status</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Kategori</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody wire:loading.class="hidden" class="divide-y divide-slate-100">
                        @foreach($chunk as $kategori)
                        <tr class="hover:bg-slate-50 transition-colors {{ !$kategori->is_active ? 'bg-slate-50/50' : '' }}">
                            <td class="px-6 py-4 text-center">
                                <button wire:click="toggleActive({{ $kategori->id }})" class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 {{ $kategori->is_active ? 'bg-blue-600' : 'bg-slate-200' }}">
                                    <span class="sr-only">Toggle Active</span>
                                    <span aria-hidden="true" class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $kategori->is_active ? 'translate-x-4' : 'translate-x-0' }}"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 font-medium {{ $kategori->is_active ? 'text-slate-900' : 'text-slate-400 line-through' }}">{{ $kategori->nama_kategori }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @can('manage kategori')
                                <button wire:click="edit({{ $kategori->id }})" class="text-amber-600 hover:text-amber-700 font-medium" title="Edit">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="delete({{ $kategori->id }})" wire:confirm="Yakin ingin menghapus kategori ini?" class="text-red-600 hover:text-red-700 font-medium" title="Hapus">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @else
                                <span class="text-xs text-slate-400 italic">No Access</span>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <!-- Skeleton Loading -->
                    <tbody wire:loading class="divide-y divide-slate-100">
                        @for($i = 0; $i < 5; $i++)
                        <tr>
                            <td class="px-6 py-4 text-center"><div class="h-5 w-9 skeleton mx-auto"></div></td>
                            <td class="px-6 py-4"><div class="h-5 w-32 skeleton"></div></td>
                            <td class="px-6 py-4 text-right"><div class="h-5 w-16 skeleton ml-auto"></div></td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-10 text-center text-slate-500 italic text-sm">
            Belum ada data kategori.
        </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $kategoris->links() }}
    </div>

    <!-- Modal Form -->
    @if($showModal)
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h4 class="text-lg font-bold text-slate-800">{{ $isEdit ? 'Edit Kategori' : 'Tambah Kategori' }}</h4>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Kategori</label>
                        <input wire:model="nama_kategori" type="text" placeholder="Contoh: Makanan, Minuman" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        @error('nama_kategori') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 flex justify-end space-x-3 rounded-b-2xl">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                <button wire:click="save" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold transition-colors shadow-lg shadow-blue-200">
                    Simpan
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
