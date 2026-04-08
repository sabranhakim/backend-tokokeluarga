<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $isEdit = false;

    // Form fields
    public $supplierId;
    public $nama_supplier, $alamat, $no_telp;

    protected $rules = [
        'nama_supplier' => 'required|min:3',
        'alamat' => 'required',
        'no_telp' => 'required|numeric',
    ];

    public function with()
    {
        return [
            'suppliers' => Supplier::where('nama_supplier', 'like', '%' . $this->search . '%')
                ->orWhere('no_telp', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetFields();
        $this->showModal = true;
    }

    public function resetFields()
    {
        $this->supplierId = null;
        $this->nama_supplier = '';
        $this->alamat = '';
        $this->no_telp = '';
        $this->isEdit = false;
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        $this->supplierId = $supplier->id;
        $this->nama_supplier = $supplier->nama_supplier;
        $this->alamat = $supplier->alamat;
        $this->no_telp = $supplier->no_telp;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->isEdit) {
            Supplier::find($this->supplierId)->update($validated);
            $message = 'Supplier berhasil diperbarui';
        } else {
            Supplier::create($validated);
            $message = 'Supplier berhasil ditambahkan';
        }

        $this->showModal = false;
        $this->dispatch('notify', $message);
    }

    public function delete($id)
    {
        try {
            Supplier::destroy($id);
            $this->dispatch('notify', 'Supplier berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Supplier tidak dapat dihapus because masih memiliki riwayat transaksi.');
        }
    }
};
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Manajemen Supplier</h3>
            <p class="text-sm text-slate-500">Kelola data vendor dan pemasok barang</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all" placeholder="Cari supplier...">
            </div>
            <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center whitespace-nowrap">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Supplier
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Supplier</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Telp</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Alamat</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $supplier->nama_supplier }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 font-mono">{{ $supplier->no_telp }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 truncate max-w-xs">{{ $supplier->alamat }}</td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button wire:click="edit({{ $supplier->id }})" class="text-amber-600 hover:text-amber-700 font-medium">Edit</button>
                            <button wire:click="delete({{ $supplier->id }})" wire:confirm="Yakin ingin menghapus supplier ini?" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-slate-500 italic text-sm">Belum ada data supplier.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $suppliers->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($showModal)
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h4 class="text-lg font-bold text-slate-800">{{ $isEdit ? 'Edit Supplier' : 'Tambah Supplier' }}</h4>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Supplier</label>
                    <input wire:model="nama_supplier" type="text" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    @error('nama_supplier') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">No. Telepon</label>
                    <input wire:model="no_telp" type="text" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    @error('no_telp') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                    <textarea wire:model="alamat" rows="3" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all"></textarea>
                    @error('alamat') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
