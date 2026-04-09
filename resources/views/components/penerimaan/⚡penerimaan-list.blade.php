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
            'penerimaans' => PenerimaanBarang::with(['supplier', 'user'])
                ->where('no_terima', 'like', '%' . $this->search . '%')
                ->orWhereHas('supplier', function($query) {
                    $query->where('nama_supplier', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
        ];
    }

    public function mount()
    {
        // Allowed
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $this->authorize('manage penerimaan');
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
            @can('manage penerimaan')
            <a href="{{ route('penerimaan.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center whitespace-nowrap shadow-lg shadow-blue-100">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Penerimaan
            </a>
            @endcan
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
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Petugas</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($penerimaans as $penerimaan)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-mono text-sm font-bold text-slate-700">{{ $penerimaan->no_terima }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $penerimaan->tgl_terima->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $penerimaan->supplier->nama_supplier }}</td>
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
                            <a href="{{ route('penerimaan.show', $penerimaan) }}" class="text-blue-600 hover:text-blue-700 font-bold text-sm">Detail</a>
                            @if($penerimaan->status_verifikasi == 'pending')
                                @can('manage penerimaan')
                                <button wire:click="delete({{ $penerimaan->id }})" wire:confirm="Yakin ingin menghapus data ini?" class="text-red-600 hover:text-red-700 font-bold text-sm">Hapus</button>
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
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $penerimaans->links() }}
        </div>
    </div>
</div>
