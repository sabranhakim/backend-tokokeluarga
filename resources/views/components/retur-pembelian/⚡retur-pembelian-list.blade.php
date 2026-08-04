<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ReturPembelian;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';

    public function mount()
    {
        if (! Gate::allows('view retur_pembelian') && ! auth()->user()->hasAnyRole(['admin', 'staff'])) {
            session()->flash('error', 'Anda tidak memiliki hak akses ke riwayat retur pembelian.');
            return $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function with()
    {
        return [
            'returPembelians' => ReturPembelian::with(['supplier', 'user'])
                ->when($this->filterDateFrom, fn ($q) => $q->whereDate('tgl_retur', '>=', $this->filterDateFrom))
                ->when($this->filterDateTo, fn ($q) => $q->whereDate('tgl_retur', '<=', $this->filterDateTo))
                ->where(function ($query) {
                    $query->where('no_retur', 'like', '%' . $this->search . '%')
                        ->orWhere('keterangan', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
        ];
    }

    public function updatingSearch()
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
        if (! Gate::allows('delete retur_pembelian') && ! auth()->user()->hasRole('admin')) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menghapus data ini.');
            return;
        }
        $returPembelian = ReturPembelian::findOrFail($id);
        $returPembelian->delete();
        $this->dispatch('notify', 'Data retur pembelian berhasil dihapus');
    }
};
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Riwayat Retur Pembelian</h3>
            <p class="text-sm text-slate-500">Barang yang dikembalikan ke supplier</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all" placeholder="Cari no. retur atau keterangan...">
            </div>
            @can('create retur_pembelian')
            <a href="{{ route('retur-pembelian.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center whitespace-nowrap shadow-lg shadow-blue-100">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Retur Pembelian
            </a>
            @endcan
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-4 items-center">
        <input wire:model.live="filterDateFrom" type="date" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Dari Tanggal">
        <p>s/d</p>
        <input wire:model.live="filterDateTo" type="date" class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Sampai Tanggal">

        @if($filterDateFrom || $filterDateTo)
        <button wire:click="$set('filterDateFrom', ''); $set('filterDateTo', '')" class="px-3 py-2 text-sm text-red-600 hover:text-red-700 font-medium hover:bg-red-50 rounded-lg transition-colors">
            Reset Filter
        </button>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Retur</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Petugas</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="hidden" class="divide-y divide-slate-100">
                    @forelse($returPembelians as $returPembelian)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-mono text-sm font-bold text-slate-700">{{ $returPembelian->no_retur }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $returPembelian->tgl_retur->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $returPembelian->supplier->nama_supplier ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $returPembelian->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 max-w-xs truncate">{{ $returPembelian->keterangan ?: '-' }}</td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="{{ route('retur-pembelian.show', $returPembelian) }}" class="text-blue-600 hover:text-blue-700 font-bold text-sm" title="Detail">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @can('delete retur_pembelian')
                            <button wire:click="delete('{{ $returPembelian->id }}')" wire:confirm="Yakin ingin menghapus data ini?" class="text-red-600 hover:text-red-700 font-bold text-sm" title="Hapus">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-500 italic text-sm">Belum ada riwayat retur pembelian.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tbody wire:loading class="divide-y divide-slate-100">
                    @for($i = 0; $i < 5; $i++)
                    <tr>
                        <td class="px-6 py-4"><div class="h-5 w-32 skeleton font-mono"></div></td>
                        <td class="px-6 py-4"><div class="h-4 w-20 skeleton"></div></td>
                        <td class="px-6 py-4"><div class="h-5 w-24 skeleton"></div></td>
                        <td class="px-6 py-4"><div class="h-5 w-24 skeleton"></div></td>
                        <td class="px-6 py-4"><div class="h-4 w-40 skeleton"></div></td>
                        <td class="px-6 py-4 text-right"><div class="h-6 w-16 skeleton ml-auto"></div></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $returPembelians->links() }}
        </div>
    </div>
</div>
