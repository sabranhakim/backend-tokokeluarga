<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StockOpname;
use App\Services\StockOpnameService;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function with()
    {
        return [
            'stockOpnames' => StockOpname::query()
                ->with(['user'])
                ->when($this->search !== '', function ($query) {
                    $query->where('no_opname', 'like', '%' . $this->search . '%')
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

    public function apply(string $id, StockOpnameService $service)
    {
        try {
            $service->finalize($id);
            session()->flash('success', 'Stock opname berhasil diterapkan dan stok telah disesuaikan.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function delete(StockOpname $stockOpname)
    {
        $stockOpname->delete();
        session()->flash('success', 'Data stock opname berhasil dihapus.');
    }
};
?>
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Stock Opname</h2>
            <p class="text-sm text-slate-500 mt-1">Pencatatan dan penyesuaian stok fisik barang.</p>
        </div>
        <div class="flex items-center gap-3">
            <input wire:model.live.debounce.300ms="search"
                   type="text"
                   placeholder="Cari nomor opname..."
                   class="px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm">
            <a href="{{ route('stock-opname.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 transition-all active:scale-95 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Opname
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/60 text-slate-400 text-[10px] font-black uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-4">No. Opname</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Petugas</th>
                        <th class="px-6 py-4 text-right">Total Selisih</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($stockOpnames as $opname)
                    <tr class="hover:bg-slate-50/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-mono text-xs font-bold text-blue-600">{{ $opname->no_opname }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $opname->keterangan ?? 'Tanpa keterangan' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $opname->tgl_opname->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-slate-700">{{ $opname->user?->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-bold {{ $opname->total_selisih == 0 ? 'text-slate-500' : ($opname->total_selisih > 0 ? 'text-green-600' : 'text-red-600') }}">
                                {{ $opname->total_selisih > 0 ? '+' : '' }}{{ number_format($opname->total_selisih) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php $draft = $opname->status === 'draft'; @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $draft ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }}">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $draft ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                                {{ $draft ? 'Draft' : 'Selesai' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2" wire:loading.class="opacity-50" wire:target="apply({{ $opname->getKey() }})">
                                <a href="{{ route('stock-opname.show', $opname->getKey()) }}"
                                   class="p-2 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @if($draft)
                                <button wire:click="apply('{{ $opname->getKey() }}')" wire:confirm="Terapkan penyesuaian stok untuk stock opname ini?"
                                        class="p-2 rounded-lg text-emerald-400 hover:text-emerald-600 hover:bg-emerald-50 transition-all" title="Terapkan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                @endif
                                <button wire:click="delete('{{ $opname->getKey() }}')" wire:confirm="Hapus stock opname ini?"
                                        class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <p class="font-semibold">Belum ada data stock opname</p>
                                <p class="text-sm">Klik "Buat Opname" untuk memulai pencatatan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stockOpnames->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $stockOpnames->links() }}
        </div>
        @endif
    </div>
</div>
