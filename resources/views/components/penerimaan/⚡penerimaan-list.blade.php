<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PenerimaanBarang;
use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $filterSupplier = '';
    public $filterStatus = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';

    public function mount()
    {
        if (!Gate::allows('view penerimaan') && !auth()->user()->hasAnyRole(['admin', 'staff'])) {
            session()->flash('error', 'Anda tidak memiliki hak akses ke riwayat penerimaan.');
            return $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function with()
    {
        return [
            'penerimaans' => PenerimaanBarang::with(['supplier', 'user'])
                ->when($this->filterSupplier, fn($q) => $q->where('supplier_id', $this->filterSupplier))
                ->when($this->filterStatus, fn($q) => $q->where('status_verifikasi', $this->filterStatus))
                ->when($this->filterDateFrom, fn($q) => $q->whereDate('tgl_terima', '>=', $this->filterDateFrom))
                ->when($this->filterDateTo, fn($q) => $q->whereDate('tgl_terima', '<=', $this->filterDateTo))
                ->where(function($query) {
                    $query->where('no_terima', 'like', '%' . $this->search . '%')
                        ->orWhereHas('supplier', function($q) {
                            $q->where('nama_supplier', 'like', '%' . $this->search . '%');
                        });
                })
                ->latest()
                ->paginate(10),
            'suppliers' => Supplier::orderBy('nama_supplier')->get(),
        ];
    }

    public function updatingSearch()
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

    public function updatingFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        if (!Gate::allows('delete penerimaan') && !auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menghapus data ini.');
            return;
        }
        $penerimaan = PenerimaanBarang::findOrFail($id);

        // Optionally: adjust stock back if needed?
        // For simplicity now, just delete.
        $penerimaan->delete();

        $this->dispatch('notify', 'Data penerimaan berhasil dihapus');
    }
};
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Riwayat Penerimaan Barang</h3>
            <p class="text-sm text-slate-500">Daftar transaksi barang masuk dari supplier</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all" placeholder="Cari no. terima atau supplier...">
            </div>
            @can('view trash')
            <a href="{{ route('trash.penerimaan.index') }}" class="text-slate-500 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-all" title="Buka Trash">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </a>
            @endcan
            @can('create penerimaan')
            <a href="{{ route('penerimaan.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center whitespace-nowrap shadow-lg shadow-blue-100">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Penerimaan
            </a>
            @endcan
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-4 items-center">
        <select wire:model.live="filterSupplier" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="">Semua Supplier</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->getKey() }}">{{ $supplier->nama_supplier }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="verified">Verified</option>
        </select>

        <input wire:model.live="filterDateFrom" type="date" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Dari Tanggal">
        <p>s/d</p>
        <input wire:model.live="filterDateTo" type="date" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Sampai Tanggal">

        @if($filterSupplier || $filterStatus || $filterDateFrom || $filterDateTo)
        <button wire:click="$set('filterSupplier', ''); $set('filterStatus', ''); $set('filterDateFrom', ''); $set('filterDateTo', '')" class="px-3 py-2 text-sm text-red-600 hover:text-red-700 font-medium hover:bg-red-50 rounded-lg transition-colors">
            Reset Filter
        </button>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Terima</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Petugas</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="hidden" class="divide-y divide-slate-100">
                    @forelse($penerimaans as $penerimaan)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-mono text-sm font-bold text-slate-700">{{ $penerimaan->no_terima }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $penerimaan->tgl_terima->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-slate-900 font-medium">
                            {{ $penerimaan->supplier->nama_supplier }}
                            @if($penerimaan->supplier->trashed())
                                <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-[10px] font-bold rounded uppercase">Deleted</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $penerimaan->user->name }}</td>
                        <td class="px-6 py-4">
                            @if($penerimaan->status_verifikasi == 'verified')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    Verified
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="{{ route('penerimaan.show', $penerimaan) }}" class="text-blue-600 hover:text-blue-700 font-bold text-sm" title="Detail">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @if($penerimaan->status_verifikasi == 'pending')
                                @can('create penerimaan')
                                <a href="{{ route('penerimaan.edit', $penerimaan) }}" class="text-amber-600 hover:text-amber-700 font-bold text-sm" title="Edit">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endcan
                                @can('delete penerimaan')
                                <button wire:click="delete('{{ $penerimaan->getKey() }}')" wire:confirm="Yakin ingin menghapus data ini?" class="text-red-600 hover:text-red-700 font-bold text-sm" title="Hapus">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endcan
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-500 italic text-sm">Belum ada riwayat penerimaan barang.</td>
                    </tr>
                    @endforelse
                </tbody>
                <!-- Skeleton Loading -->
                <tbody wire:loading class="divide-y divide-slate-100">
                    @for($i = 0; $i < 5; $i++)
                    <tr>
                        <td class="px-6 py-4"><div class="h-5 w-32 skeleton font-mono"></div></td>
                        <td class="px-6 py-4"><div class="h-4 w-20 skeleton"></div></td>
                        <td class="px-6 py-4"><div class="h-5 w-40 skeleton"></div></td>
                        <td class="px-6 py-4"><div class="h-4 w-24 skeleton"></div></td>
                        <td class="px-6 py-4"><div class="h-6 w-16 skeleton rounded-full"></div></td>
                        <td class="px-6 py-4 text-right"><div class="h-6 w-16 skeleton ml-auto"></div></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $penerimaans->links() }}
        </div>
    </div>
</div>
