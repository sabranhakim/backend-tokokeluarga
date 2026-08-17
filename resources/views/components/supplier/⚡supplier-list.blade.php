<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Barang;
use App\Models\PenerimaanBarang;
use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
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

    public function mount()
    {
        if (!Gate::allows('view supplier') && !auth()->user()->hasAnyRole(['admin', 'staff'])) {
            session()->flash('error', 'Anda tidak memiliki hak akses ke data supplier.');
            return $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function with()
    {
        return [
            'suppliers' => Supplier::withoutGlobalScope('active')
                ->withCount(['penerimaanBarangs', 'barangs'])
                ->withMax('penerimaanBarangs', 'tgl_terima')
                ->where(function($query) {
                    $query->where('nama_supplier', 'like', '%' . $this->search . '%')
                        ->orWhere('no_telp', 'like', '%' . $this->search . '%');
                })
                ->when($this->filterStatus === 'aktif', fn($q) => $q->where('is_active', true))
                ->when($this->filterStatus === 'nonaktif', fn($q) => $q->where('is_active', false))
                ->latest()
                ->paginate(10),
            'totalSupplier' => Supplier::withoutGlobalScope('active')->count(),
            'supplierAktif' => Supplier::where('is_active', true)->count(),
            'totalBarangTersuplai' => Barang::withoutGlobalScope('active')->whereNotNull('supplier_id')->count(),
            'totalPenerimaan' => PenerimaanBarang::count(),
        ];
    }

    public function toggleActive($id)
    {
        if (!Gate::allows('manage supplier') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk mengubah status supplier.');
            return;
        }
        $supplier = Supplier::withoutGlobalScope('active')->findOrFail($id);
        $supplier->update(['is_active' => !$supplier->is_active]);
        $this->dispatch('notify', 'Status supplier berhasil diubah');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        if (!Gate::allows('manage supplier') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menambah supplier.');
            return;
        }
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
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        if (!Gate::allows('manage supplier') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk mengedit supplier.');
            return;
        }
        $supplier = Supplier::withoutGlobalScope('active')->findOrFail($id);
        $this->supplierId = $supplier->getKey();
        $this->nama_supplier = $supplier->nama_supplier;
        $this->alamat = $supplier->alamat;
        $this->no_telp = $supplier->no_telp;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        if (!Gate::allows('manage supplier') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menyimpan data ini.');
            return;
        }
        $validated = $this->validate();

        if ($this->isEdit) {
            Supplier::withoutGlobalScope('active')->find($this->supplierId)->update($validated);
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
        if (!Gate::allows('manage supplier') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menghapus supplier.');
            return;
        }
        try {
            Supplier::withoutGlobalScope('active')->find($id)?->delete();
            $this->dispatch('notify', 'Supplier berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('notify', 'Supplier tidak dapat dihapus karena masih memiliki riwayat transaksi.');
        }
    }
};
?>

<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h3 class="text-2xl font-black text-slate-800">Manajemen Supplier</h3>
            <p class="text-sm text-slate-500 mt-1">Kelola data vendor dan pemasok barang toko.</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all" placeholder="Cari nama atau telepon...">
            </div>
            @can('view trash')
            <a href="{{ route('trash.supplier.index') }}" class="text-slate-500 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-all" title="Buka Trash">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </a>
            @endcan
            @can('manage supplier')
            <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl font-bold transition-colors flex items-center whitespace-nowrap shadow-lg shadow-blue-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Supplier
            </button>
            @endcan
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 relative overflow-hidden group">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Supplier</p>
                    <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalSupplier) }}</h3>
                </div>
                <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 relative overflow-hidden group">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Supplier Aktif</p>
                    <h3 class="text-3xl font-black text-emerald-600">{{ number_format($supplierAktif) }}</h3>
                </div>
                <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 relative overflow-hidden group">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 w-24 h-24 bg-amber-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Barang Tersuplai</p>
                    <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalBarangTersuplai) }}</h3>
                </div>
                <div class="p-2 bg-amber-100 rounded-lg text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 relative overflow-hidden group">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 w-24 h-24 bg-indigo-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Transaksi Masuk</p>
                    <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalPenerimaan) }}</h3>
                </div>
                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="flex flex-wrap items-center gap-3">
        <select wire:model.live="filterStatus" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="">Semua Status</option>
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
        </select>
        @if($filterStatus)
        <button wire:click="$set('filterStatus', '')" class="px-3 py-2 text-sm text-red-600 hover:text-red-700 font-medium hover:bg-red-50 rounded-lg transition-colors">
            Reset Filter
        </button>
        @endif
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/60">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Telp</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Alamat</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Barang</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Transaksi</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Terakhir</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="hidden" class="divide-y divide-slate-100">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-slate-50/50 transition-colors {{ !$supplier->is_active ? 'bg-slate-50/50' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black mr-3 {{ $supplier->is_active ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-400' }}">
                                    {{ strtoupper(substr($supplier->nama_supplier, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold {{ $supplier->is_active ? 'text-slate-900' : 'text-slate-400' }}">{{ $supplier->nama_supplier }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $supplier->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1 {{ $supplier->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 font-mono">{{ $supplier->no_telp }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate" title="{{ $supplier->alamat }}">{{ $supplier->alamat }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center min-w-8 px-2 py-1 rounded-lg text-xs font-black {{ $supplier->barangs_count > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-400' }}">
                                {{ number_format($supplier->barangs_count) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center min-w-8 px-2 py-1 rounded-lg text-xs font-black {{ $supplier->penerimaan_barangs_count > 0 ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-100 text-slate-400' }}">
                                {{ number_format($supplier->penerimaan_barangs_count) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">
                            @if($supplier->penerimaan_barangs_max_tgl_terima)
                                {{ \Carbon\Carbon::parse($supplier->penerimaan_barangs_max_tgl_terima)->format('d/m/Y') }}
                            @else
                                <span class="text-slate-300 italic">Belum ada</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                @can('manage supplier')
                                <button wire:click="edit('{{ $supplier->getKey() }}')" class="p-2 rounded-lg text-amber-600 hover:bg-amber-50 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="delete('{{ $supplier->getKey() }}')" wire:confirm="Yakin ingin menghapus supplier ini?" class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @else
                                <span class="text-xs text-slate-400 italic">No Access</span>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <p class="font-semibold text-slate-500">Belum ada data supplier.</p>
                            <p class="text-sm text-slate-400">Klik "Tambah Supplier" untuk mencatat vendor baru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <!-- Skeleton Loading -->
                <tbody wire:loading class="divide-y divide-slate-100">
                    @for($i = 0; $i < 5; $i++)
                    <tr>
                        <td class="px-6 py-4"><div class="flex items-center"><div class="w-10 h-10 skeleton rounded-xl mr-3"></div><div class="space-y-2"><div class="h-4 w-32 skeleton"></div><div class="h-3 w-16 skeleton"></div></div></div></td>
                        <td class="px-6 py-4"><div class="h-4 w-28 skeleton font-mono"></div></td>
                        <td class="px-6 py-4"><div class="h-4 w-40 skeleton"></div></td>
                        <td class="px-6 py-4 text-center"><div class="h-6 w-8 skeleton mx-auto"></div></td>
                        <td class="px-6 py-4 text-center"><div class="h-6 w-8 skeleton mx-auto"></div></td>
                        <td class="px-6 py-4"><div class="h-4 w-20 skeleton"></div></td>
                        <td class="px-6 py-4 text-right"><div class="h-8 w-20 skeleton ml-auto"></div></td>
                    </tr>
                    @endfor
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
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 flex justify-between items-center">
                <h4 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isEdit ? 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' : 'M12 6v6m0 0v6m0-6h6m-6 0H6' }}"></svg>
                    {{ $isEdit ? 'Edit Supplier' : 'Tambah Supplier' }}
                </h4>
                <button wire:click="$set('showModal', false)" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Nama Supplier
                    </label>
                    <input wire:model="nama_supplier" type="text" placeholder="cth. Toko Makmur Jaya" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('nama_supplier') border-red-500 @enderror">
                    @error('nama_supplier') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        No. Telepon
                    </label>
                    <input wire:model="no_telp" type="text" placeholder="cth. 081234567890" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('no_telp') border-red-500 @enderror">
                    @error('no_telp') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Alamat
                    </label>
                    <textarea wire:model="alamat" rows="3" placeholder="Alamat lengkap supplier..." class="w-full px-4 py-2.5 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all resize-none @error('alamat') border-red-500 @enderror"></textarea>
                    @error('alamat') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 flex justify-end space-x-3">
                <button wire:click="$set('showModal', false)" class="px-4 py-2.5 text-slate-600 hover:text-slate-800 font-medium rounded-lg hover:bg-slate-100 transition-colors">Batal</button>
                <button wire:click="save" wire:loading.attr="disabled" wire:target="save" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold transition-colors shadow-lg shadow-blue-200 disabled:opacity-70 flex items-center">
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save" class="flex items-center">
                        <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
