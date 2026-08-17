<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Barang;
use App\Models\BarangStok;
use App\Models\Kategori;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $filterKategori = '';
    public $filterSupplier = '';
    public $filterStatus = '';
    public $filterStok = '';
    public $showModal = false;
    public $isEdit = false;

    // Form fields
    public $barangId;
    public $kode_barang, $nama_barang, $kategori_id, $supplier_id, $satuan, $isi = 1, $harga_beli, $harga_jual, $stok = 0, $stok_minimal = 10;

    protected $rules = [
        'kode_barang' => 'required|unique:barangs,kode_barang',
        'nama_barang' => 'required',
        'kategori_id' => 'required|exists:kategoris,id_kategori',
        'supplier_id' => 'nullable|exists:suppliers,id_supplier',
        'satuan' => 'required',
        'isi' => 'required|integer|min:1',
        'harga_beli' => 'required|numeric|min:0',
        'harga_jual' => 'required|numeric|min:0',
        'stok_minimal' => 'required|numeric|min:0',
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
                ->when($this->filterKategori, fn($q) => $q->where('kategori_id', $this->filterKategori))
                ->when($this->filterSupplier, fn($q) => $q->where('supplier_id', $this->filterSupplier))
                ->when($this->filterStatus !== '', fn($q) => $q->where('is_active', $this->filterStatus))
                ->when($this->filterStok, function($q) {
                    match ($this->filterStok) {
                        'kritis' => $q->where('stok', 0),
                        'rendah' => $q->whereColumn('stok', '<=', 'stok_minimal')->where('stok', '>', 0),
                        'normal' => $q->whereColumn('stok', '>', 'stok_minimal'),
                        default => null,
                    };
                })
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

    public function updatingFilterKategori()
    {
        $this->resetPage();
    }

    public function updatingFilterSupplier()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterStok()
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
        $maxNumber = (int) Barang::withoutGlobalScope('active')
            ->withTrashed()
            ->get('kode_barang')
            ->map(function ($barang) {
                preg_match('/BRG-(\d+)/', $barang->kode_barang, $matches);
                return isset($matches[1]) ? (int) $matches[1] : 0;
            })
            ->max();

        $this->kode_barang = 'BRG-' . str_pad((string) ($maxNumber + 1), 3, '0', STR_PAD_LEFT);
    }

    public function ensureUniqueKodeBarang()
    {
        if (empty(trim($this->kode_barang ?? ''))) {
            $this->generateKodeBarang();
            return;
        }

        preg_match('/BRG-(\d+)/', $this->kode_barang, $matches);
        if (!isset($matches[1])) {
            $this->generateKodeBarang();
            return;
        }

        $next = (int) $matches[1];
        while (Barang::withoutGlobalScope('active')
            ->withTrashed()
            ->where('kode_barang', 'BRG-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT))
            ->exists()) {
            $next++;
        }
        $this->kode_barang = 'BRG-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public function resetFields()
    {
        $this->barangId = null;
        $this->kode_barang = '';
        $this->nama_barang = '';
        $this->kategori_id = '';
        $this->supplier_id = '';
        $this->satuan = '';
        $this->isi = 1;
        $this->harga_beli = null;
        $this->harga_jual = null;
        $this->stok = 0;
        $this->stok_minimal = 10;
        $this->isEdit = false;
    }

    public function edit($id)
    {
        if (!Gate::allows('manage barang') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk mengedit barang.');
            return;
        }
        $barang = Barang::withoutGlobalScope('active')->findOrFail($id);
        $this->barangId = $barang->getKey();
        $this->kode_barang = $barang->kode_barang;
        $this->nama_barang = $barang->nama_barang;
        $this->kategori_id = $barang->kategori_id;
        $this->supplier_id = $barang->supplier_id;
        $this->satuan = $barang->satuan;
        $this->isi = $barang->isi ?? 1;
        $this->harga_beli = $barang->harga_beli;
        $this->harga_jual = $barang->harga_jual;
        $this->stok = $barang->stok;
        $this->stok_minimal = $barang->stok_minimal;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        if (!Gate::allows('manage barang') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menyimpan data ini.');
            return;
        }

        if (!$this->isEdit) {
            $this->ensureUniqueKodeBarang();
        }

        $this->harga_beli = is_numeric($this->harga_beli) ? (int) $this->harga_beli : $this->harga_beli;
        $this->harga_jual = is_numeric($this->harga_jual) ? (int) $this->harga_jual : $this->harga_jual;

        $validationRules = $this->rules;
        if ($this->isEdit) {
            $validationRules['kode_barang'] = 'required|unique:barangs,kode_barang,' . $this->barangId . ',id_barang';
        }

        $validated = $this->validate($validationRules);

        DB::transaction(function () use ($validated) {
            if ($this->isEdit) {
                $barang = Barang::withoutGlobalScope('active')->find($this->barangId);
                $validated['stok'] = $barang->stok;
                $barang->update($validated);
                $this->dispatch('notify', 'Barang berhasil diperbarui');
            } else {
                $validated['stok'] = 0;
                $barang = Barang::create($validated);
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
        Barang::withoutGlobalScope('active')->find($id)?->delete();
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

    <div class="flex flex-wrap gap-3 mb-4">
        <select wire:model.live="filterKategori" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="">Semua Kategori</option>
            @foreach($kategoris as $kategori)
                <option value="{{ $kategori->getKey() }}">{{ $kategori->nama_kategori }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterSupplier" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="">Semua Supplier</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->getKey() }}">{{ $supplier->nama_supplier }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="">Semua Status</option>
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
        </select>

        <select wire:model.live="filterStok" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="">Semua Stok</option>
            <option value="kritis">Stok Habis (0)</option>
            <option value="rendah">Stok Rendah (<= Min)</option>
            <option value="normal">Stok Normal</option>
        </select>

        @if($filterKategori || $filterSupplier || $filterStatus !== '' || $filterStok)
        <button wire:click="$set('filterKategori', ''); $set('filterSupplier', ''); $set('filterStatus', ''); $set('filterStok', '')" class="px-3 py-2 text-sm text-red-600 hover:text-red-700 font-medium hover:bg-red-50 rounded-lg transition-colors">
            Reset Filter
        </button>
        @endif
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
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Batch</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="hidden" class="divide-y divide-slate-100">
                    @forelse($barangs as $barang)
                    <tr class="hover:bg-slate-50 transition-colors {{ !$barang->is_active ? 'bg-slate-50/50' : '' }}">
                        <td class="px-6 py-4">
                            <button wire:click="toggleActive('{{ $barang->getKey() }}')" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 {{ $barang->is_active ? 'bg-blue-600' : 'bg-slate-200' }}">
                                <span class="sr-only">Toggle Active</span>
                                <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $barang->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs font-bold px-2 py-1 bg-slate-100 text-slate-700 rounded">{{ $barang->kode_barang }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium {{ $barang->is_active ? 'text-slate-900' : 'text-slate-400 line-through' }}">{{ $barang->nama_barang }}</div>
                            <div class="text-xs text-slate-500">{{ $barang->satuan }} @if($barang->isi > 1)({{ $barang->isi }} pcs/{{ $barang->satuan }})@endif</div>
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
                        <td class="px-6 py-4 text-center">
                            @php
                                $batchCount = \App\Models\BarangStok::where('barang_id', $barang->getKey())->where('stok', '>', 0)->count();
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $batchCount > 0 ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-400 border-slate-200' }}">
                                {{ $batchCount }} batch
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button @click.stop="open = !open" type="button" title="Aksi"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                </button>

                                <div x-show="open" @click.outside="open = false"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    x-cloak
                                    class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-slate-100 z-50 overflow-hidden py-1">
                                    <a href="{{ route('barang.show', $barang->getKey()) }}" @click="open = false" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors" title="Detail Barang">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Detail
                                    </a>
                                    <a href="{{ route('barang.history', $barang->getKey()) }}" @click="open = false" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors" title="Riwayat Stok">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Riwayat Stok
                                    </a>
                                    @can('manage barang')
                                    <div class="border-t border-slate-100 my-1"></div>
                                    <button wire:click="edit('{{ $barang->getKey() }}')" @click="open = false" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-amber-600 hover:bg-amber-50 transition-colors" title="Edit Barang">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </button>
                                    <button wire:click="delete('{{ $barang->getKey() }}')" wire:confirm="Yakin ingin menghapus barang ini?" @click="open = false" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors" title="Hapus Barang">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                    @endcan
                                </div>
                            </div>
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
                        <div class="relative">
                            <input wire:model="kode_barang" type="text" readonly placeholder="Otomatis" class="w-full px-4 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed outline-none">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-emerald-600 font-semibold">Auto</span>
                        </div>
                        @error('kode_barang') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Kategori -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Kategori</label>
                        <select wire:model="kategori_id" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('kategori_id') border-red-500 @enderror">
                            <option value="">Pilih Kategori</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->getKey() }}">{{ $kategori->nama_kategori }}</option>
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
                                <option value="{{ $supplier->getKey() }}">{{ $supplier->nama_supplier }}</option>
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

                    <!-- Isi per Kemasan -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Isi per {{ $satuan ?: 'Kemasan' }} (pcs)</label>
                        <div class="relative">
                            <input wire:model="isi" type="number" min="1" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('isi') border-red-500 @enderror">
                            <div class="mt-1 text-xs text-slate-400">
                                Jumlah pcs dalam 1 {{ $satuan ?: 'kemasan' }}
                                @if($isi > 1)
                                <span class="text-blue-600 font-medium">→ 1 {{ $satuan ?: 'kemasan' }} = {{ $isi }} pcs</span>
                                @endif
                            </div>
                        </div>
                        @error('isi') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Stok (read-only, dikelola via penerimaan) -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Stok Saat Ini</label>
                        <div class="flex items-center gap-2">
                            <input wire:model="stok" type="number" readonly disabled class="w-full px-4 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed outline-none">
                            <span class="text-xs text-slate-400 italic">Dikelola via penerimaan barang</span>
                        </div>
                        @error('stok') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Stok Minimal -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Batas Stok Minimal (Alert)</label>
                        <input wire:model="stok_minimal" type="number" min="0" step="1" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('stok_minimal') border-red-500 @enderror">
                        @error('stok_minimal') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Harga Beli -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Harga Beli</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 font-bold">Rp</span>
                            <input wire:model="harga_beli" type="number" min="0" step="1" placeholder="0" class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('harga_beli') border-red-500 @enderror">
                        </div>
                        @error('harga_beli') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Harga Jual -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700">Harga Jual</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 font-bold">Rp</span>
                            <input wire:model="harga_jual" type="number" min="0" step="1" placeholder="0" class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('harga_jual') border-red-500 @enderror">
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
