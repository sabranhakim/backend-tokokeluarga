<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PenerimaanBarang;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function with()
    {
        return [
            'penerimaans' => PenerimaanBarang::onlyTrashed()
                ->with(['supplier', 'user'])
                ->where('no_terima', 'like', '%' . $this->search . '%')
                ->orWhereHas('supplier', function($query) {
                    $query->where('nama_supplier', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function restore($id)
    {
        if (!auth()->user()->can('manage trash')) {
            return;
        }
        $penerimaan = PenerimaanBarang::withTrashed()->findOrFail($id);
        $penerimaan->restore();
        
        // Also restore details if they are soft-deleted
        $penerimaan->detailPenerimaans()->withTrashed()->restore();

        $this->dispatch('notify', 'Data penerimaan berhasil dipulihkan');
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->can('manage trash')) {
            return;
        }
        $penerimaan = PenerimaanBarang::withTrashed()->findOrFail($id);
        
        // Delete details first
        $penerimaan->detailPenerimaans()->withTrashed()->forceDelete();
        $penerimaan->forceDelete();

        $this->dispatch('notify', 'Data penerimaan berhasil dihapus permanen');
    }
};
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Trash: Penerimaan Barang</h3>
            <p class="text-sm text-slate-500">Data penerimaan yang dihapus sementara</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all" placeholder="Cari di trash...">
            </div>
            <a href="{{ route('penerimaan.index') }}" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center whitespace-nowrap">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Terima</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Dihapus Pada</th>
                        @can('manage trash')
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($penerimaans as $penerimaan)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-mono text-sm font-bold text-slate-700">{{ $penerimaan->no_terima }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $penerimaan->tgl_terima?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $penerimaan->supplier->nama_supplier }}</td>
                        <td class="px-6 py-4 text-sm text-right text-slate-600">
                            {{ $penerimaan->deleted_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        @can('manage trash')
                        <td class="px-6 py-4 text-right space-x-3">
                            <button wire:click="restore({{ $penerimaan->id }})" class="text-emerald-600 hover:text-emerald-700 font-bold text-sm">Restore</button>
                            <button wire:click="forceDelete({{ $penerimaan->id }})" wire:confirm="Yakin ingin menghapus permanen?" class="text-red-600 hover:text-red-700 font-bold text-sm">Hapus Permanen</button>
                        </td>
                        @endcan
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('manage trash') ? 5 : 4 }}" class="px-6 py-10 text-center text-slate-500 italic text-sm">Trash kosong.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $penerimaans->links() }}
        </div>
    </div>
</div>
