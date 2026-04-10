<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Barang;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function mount()
    {
        if (!auth()->user()->can('view trash')) {
            session()->flash('error', 'Anda tidak memiliki akses ke trash.');
            return $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function with()
    {
        return [
            'barangs' => Barang::onlyTrashed()
                ->with('kategori')
                ->where(function($query) {
                    $query->where('nama_barang', 'like', '%' . $this->search . '%')
                        ->orWhere('kode_barang', 'like', '%' . $this->search . '%');
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
        $barang = Barang::withTrashed()->findOrFail($id);
        $barang->restore();
        $this->dispatch('notify', 'Barang berhasil dipulihkan');
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->can('manage trash')) {
            return;
        }
        $barang = Barang::withTrashed()->findOrFail($id);
        $barang->forceDelete();
        $this->dispatch('notify', 'Barang berhasil dihapus permanen');
    }
};
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Trash: Barang</h3>
            <p class="text-sm text-slate-500">Data barang yang dihapus sementara</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all" placeholder="Cari di trash...">
            </div>
            <a href="{{ route('barang.index') }}" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center whitespace-nowrap">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Barang</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Dihapus Pada</th>
                        @can('manage trash')
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($barangs as $barang)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs font-bold px-2 py-1 bg-slate-100 text-slate-700 rounded">{{ $barang->kode_barang }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-slate-900">{{ $barang->nama_barang }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-600">{{ $barang->kategori->nama_kategori ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-slate-600">
                            {{ $barang->deleted_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        @can('manage trash')
                        <td class="px-6 py-4 text-right space-x-2">
                            <button wire:click="restore({{ $barang->id }})" class="text-emerald-600 hover:text-emerald-700 font-medium" title="Restore">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                            <button wire:click="forceDelete({{ $barang->id }})" wire:confirm="Yakin ingin menghapus permanen? Data tidak bisa dikembalikan!" class="text-red-600 hover:text-red-700 font-medium" title="Delete Permanently">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                        @endcan
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center">
                            <p class="text-slate-500 italic text-sm">Trash kosong.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $barangs->links() }}
        </div>
    </div>
</div>
