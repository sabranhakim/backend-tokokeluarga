<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $isEdit = false;

    // Form fields
    public $barangId;
    public $kode_barang, $nama_barang, $kategori_id, $supplier_id, $satuan, $harga_beli = 0, $harga_jual = 0, $stok = 0, $stok_minimal = 10, $tgl_kadaluarsa;

    protected $rules = [
        'kode_barang' => 'required|unique:barangs,kode_barang',
        'nama_barang' => 'required',
        'kategori_id' => 'required|exists:kategoris,id',
        'supplier_id' => 'nullable|exists:suppliers,id',
        'satuan' => 'required',
        'harga_beli' => 'required|numeric|min:0',
        'harga_jual' => 'required|numeric|min:0',
        'stok' => 'required|numeric|min:0',
        'stok_minimal' => 'required|numeric|min:0',
        'tgl_kadaluarsa' => 'nullable|date',
    ];

    public function mount()
    {
        // Double Layer check: Permission + Role
        if (!Gate::allows('view barang') && !auth()->user()->hasAnyRole(['admin', 'staff'])) {
            session()->flash('error', 'Anda tidak memiliki hak akses ke data barang.');
            return $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function with()
    {
        return [
            'barangs' => Barang::withoutGlobalScope('active')->with(['kategori', 'supplier'])
                ->where(function($query) {
                    $query->where('nama_barang', 'like', '%' . $this->search . '%')
                        ->orWhere('kode_barang', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
            'kategoris' => Kategori::all(),
            'suppliers' => Supplier::orderBy('nama_supplier')->get(),
        ];
    }

    public function toggleActive($id)
    {
        if (!Gate::allows('manage barang') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk mengubah status barang.');
            return;
        }
        $barang = Barang::withoutGlobalScope('active')->findOrFail($id);
        $barang->update(['is_active' => !$barang->is_active]);
        $this->dispatch('notify', 'Status barang berhasil diubah');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        if (!Gate::allows('manage barang') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menambah barang.');
            return;
        }
        $this->resetFields();
        $this->generateKodeBarang();
        $this->showModal = true;
    }

    public function generateKodeBarang()
    {
        $lastBarang = Barang::withoutGlobalScope('active')->orderBy('created_at', 'desc')->first();

        if (!$lastBarang) {
            $this->kode_barang = 'BRG-0001';
            return;
        }

        // Extract number from BRG-XXXX
        $lastCode = $lastBarang->kode_barang;
        $number = (int) substr($lastCode, 4);
        $newNumber = $number + 1;

        $this->kode_barang = 'BRG-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function resetFields()
    {
        $this->barangId = null;
        $this->kode_barang = '';
        $this->nama_barang = '';
        $this->kategori_id = '';
        $this->supplier_id = '';
        $this->satuan = '';
        $this->harga_beli = 0;
        $this->harga_jual = 0;
        $this->stok = 0;
        $this->stok_minimal = 10;
        $this->tgl_kadaluarsa = null;
        $this->isEdit = false;
    }

    public function edit($id)
    {
        if (!Gate::allows('manage barang') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk mengedit barang.');
            return;
        }
        $barang = Barang::withoutGlobalScope('active')->findOrFail($id);
        $this->barangId = $barang->id;
        $this->kode_barang = $barang->kode_barang;
        $this->nama_barang = $barang->nama_barang;
        $this->kategori_id = $barang->kategori_id;
        $this->supplier_id = $barang->supplier_id;
        $this->satuan = $barang->satuan;
        $this->harga_beli = $barang->harga_beli;
        $this->harga_jual = $barang->harga_jual;
        $this->stok = $barang->stok;
        $this->stok_minimal = $barang->stok_minimal;
        $this->tgl_kadaluarsa = $barang->tgl_kadaluarsa ? $barang->tgl_kadaluarsa->format('Y-m-d') : null;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        if (!Gate::allows('manage barang') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menyimpan data ini.');
            return;
        }
        $validationRules = $this->rules;
        if ($this->isEdit) {
            $validationRules['kode_barang'] = 'required|unique:barangs,kode_barang,' . $this->barangId;
        }

        $validated = $this->validate($validationRules);

        DB::transaction(function () use ($validated) {
            if ($this->isEdit) {
                $barang = Barang::withoutGlobalScope('active')->find($this->barangId);
                $oldStok = $barang->stok;
                $barang->update($validated);

                if ($oldStok != $validated['stok']) {
                    StockMovement::create([
                        'barang_id' => $barang->id,
                        'user_id' => auth()->id(),
                        'type' => 'adjustment',
                        'quantity' => $validated['stok'] - $oldStok,
                        'before_quantity' => $oldStok,
                        'after_quantity' => $validated['stok'],
                        'reason' => 'Koreksi Stok Manual (Edit Barang)',
                    ]);
                }
                $this->dispatch('notify', 'Barang berhasil diperbarui');
            } else {
                $barang = Barang::create($validated);

                if ($barang->stok > 0) {
                    StockMovement::create([
                        'barang_id' => $barang->id,
                        'user_id' => auth()->id(),
                        'type' => 'in',
                        'quantity' => $barang->stok,
                        'before_quantity' => 0,
                        'after_quantity' => $barang->stok,
                        'reason' => 'Saldo Awal (Inisialisasi)',
                    ]);
                }
                $this->dispatch('notify', 'Barang berhasil ditambahkan');
            }
        });

        $this->showModal = false;
    }

    public function delete($id)
    {
        if (!Gate::allows('manage barang') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menghapus barang.');
            return;
        }
        Barang::withoutGlobalScope('active')->destroy($id);
        $this->dispatch('notify', 'Barang berhasil dihapus');
    }
};
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Daftar Stok Barang</h3>
            <p class="text-sm text-slate-500">Manajemen inventaris barang and stok</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all" placeholder="Cari barang...">
            </div>
            @can('view trash')
            <a href="{{ route('trash.barang.index') }}" class="text-slate-500 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-all" title="Buka Trash">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </a>
            @endcan
            @can('manage barang')
            <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center whitespace-nowrap">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Barang
            </button>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Barang</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Harga Beli</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Harga Jual</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Stok / Min</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="hidden" class="divide-y divide-slate-100">
                    @forelse($barangs as $barang)
                    <tr class="hover:bg-slate-50 transition-colors {{ !$barang->is_active ? 'bg-slate-50/50' : '' }}">
                        <td class="px-6 py-4">
                            <button wire:click="toggleActive('{{ $barang->id }}')" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 {{ $barang->is_active ? 'bg-blue-600' : 'bg-slate-200' }}">
                                <span class="sr-only">Toggle Active</span>
                                <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $barang->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs font-bold px-2 py-1 bg-slate-100 text-slate-700 rounded">{{ $barang->kode_barang }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium {{ $barang->is_active ? 'text-slate-900' : 'text-slate-400 line-through' }}">{{ $barang->nama_barang }}</div>
                            <div class="text-xs text-slate-500">{{ $barang->satuan }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-600">{{ $barang->supplier->nama_supplier ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-slate-600 font-mono">

                            Rp {{ number_format($barang->harga_beli, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-slate-900 font-bold font-mono">
                            Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $isLow = $barang->stok <= $barang->stok_minimal;
                                $stokColor = $isLow ? 'bg-red-100 text-red-700 border-red-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200';
                            @endphp
                            <div class="flex flex-col items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $stokColor }}">
                                    {{ $barang->stok }}
                                </span>
                                <span class="text-[10px] text-slate-400 mt-1 font-bold uppercase tracking-tighter">Min: {{ $barang->stok_minimal }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('barang.show', $barang->id) }}" class="text-slate-600 hover:text-blue-600 font-medium" title="Detail Barang">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('barang.history', $barang->id) }}" class="text-blue-600 hover:text-blue-700 font-medium" title="Riwayat Stok">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </a>
                            @can('manage barang')
                            <button wire:click="edit('{{ $barang->id }}')" class="text-amber-600 hover:text-amber-700 font-medium">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button wire:click="delete('{{ $barang->id }}')" wire:confirm="Yakin ingin menghapus barang ini?" class="text-red-600 hover:text-red-700 font-medium">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @else
                            <span class="text-xs text-slate-400 italic">No Access</span>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <p class="text-slate-500 italic text-sm">Belum ada data barang.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <!-- Skeleton Loading -->
                <tbody wire:loading class="divide-y divide-slate-100">
                    @for($i = 0; $i < 5; $i++)
                    <tr>
                        <td class="px-6 py-4"><div class="h-6 w-11 skeleton"></div></td>
                        <td class="px-6 py-4"><div class="h-6 w-16 skeleton"></div></td>
                        <td class="px-6 py-4">
                            <div class="h-4 w-32 skeleton mb-2"></div>
                            <div class="h-3 w-12 skeleton"></div>
                        </td>
                        <td class="px-6 py-4"><div class="h-4 w-20 skeleton"></div></td>
                        <td class="px-6 py-4"><div class="h-4 w-24 skeleton ml-auto"></div></td>
                        <td class="px-6 py-4"><div class="h-4 w-24 skeleton ml-auto"></div></td>
                        <td class="px-6 py-4 flex justify-center"><div class="h-8 w-10 skeleton rounded-full"></div></td>
                        <td class="px-6 py-4"><div class="h-6 w-16 skeleton ml-auto"></div></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $barangs->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($showModal)
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl my-auto">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-white rounded-t-2xl sticky top-0 z-10">
                <div>
                    <h4 class="text-xl font-bold text-slate-800">{{ $isEdit ? 'Edit Data Barang' : 'Tambah Barang Baru' }}</h4>
                    <p class="text-sm text-slate-500">Lengkapi informasi detail barang di bawah ini.</p>
                </div>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-2 rounded-full hover:bg-slate-100 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit.prevent="save" class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kode Barang -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Kode Barang</label>
                        <input wire:model="kode_barang" type="text" placeholder="Contoh: BRG-001" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('kode_barang') border-red-500 @enderror">
                        @error('kode_barang') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Kategori -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Kategori</label>
                        <select wire:model="kategori_id" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('kategori_id') border-red-500 @enderror">
                            <option value="">Pilih Kategori</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                            @endforeach
                        </select>
                        @error('kategori_id') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Supplier -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Supplier Utama</label>
                        <select wire:model="supplier_id" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('supplier_id') border-red-500 @enderror">
                            <option value="">Pilih Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nama Barang -->
                    <div class="md:col-span-2 space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Nama Barang</label>
                        <input wire:model="nama_barang" type="text" placeholder="Masukkan nama barang" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('nama_barang') border-red-500 @enderror">
                        @error('nama_barang') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Satuan -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Satuan</label>
                        <input wire:model="satuan" type="text" placeholder="Contoh: Pcs, Box, Kg" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('satuan') border-red-500 @enderror">
                        @error('satuan') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Stok Awal -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Stok</label>
                        <input wire:model="stok" type="number" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('stok') border-red-500 @enderror">
                        @error('stok') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Stok Minimal -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Batas Stok Minimal (Alert)</label>
                        <input wire:model="stok_minimal" type="number" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('stok_minimal') border-red-500 @enderror">
                        @error('stok_minimal') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tgl Kadaluarsa -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Tgl. Kadaluarsa</label>
                        <input wire:model="tgl_kadaluarsa" type="date" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('tgl_kadaluarsa') border-red-500 @enderror">
                        @error('tgl_kadaluarsa') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Harga Beli -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Harga Beli</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 font-bold">Rp</span>
                            <input wire:model="harga_beli" type="number" class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('harga_beli') border-red-500 @enderror">
                        </div>
                        @error('harga_beli') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Harga Jual -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Harga Jual</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 font-bold">Rp</span>
                            <input wire:model="harga_jual" type="number" class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('harga_jual') border-red-500 @enderror">
                        </div>
                        @error('harga_jual') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-10 flex justify-end space-x-4 border-t border-slate-100 pt-6">
                    <button type="button" wire:click="$set('showModal', false)" class="px-6 py-2.5 text-slate-600 hover:text-slate-800 font-bold hover:bg-slate-50 rounded-lg transition-all">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-2.5 rounded-lg font-bold shadow-lg shadow-blue-200 transition-all active:scale-95">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Barang' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
