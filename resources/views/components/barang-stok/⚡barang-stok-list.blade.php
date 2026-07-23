<?php

use App\Models\Barang;
use App\Models\BarangStok;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $filterBarang = '';
    public $filterExpiry = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterBarang' => ['except' => ''],
        'filterExpiry' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterBarang()
    {
        $this->resetPage();
    }

    public function updatingFilterExpiry()
    {
        $this->resetPage();
    }

    public function with()
    {
        $query = BarangStok::with(['barang.kategori', 'penerimaanBarang'])
            ->where('stok', '>', 0);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', '%' . $search . '%')
                    ->orWhereHas('barang', function ($b) use ($search) {
                        $b->where('nama_barang', 'like', '%' . $search . '%')
                            ->orWhere('kode_barang', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($this->filterBarang) {
            $query->where('barang_id', $this->filterBarang);
        }

        if ($this->filterExpiry === 'expired') {
            $query->where('tgl_kadaluarsa', '<', now()->toDateString());
        } elseif ($this->filterExpiry === 'soon') {
            $query->whereBetween('tgl_kadaluarsa', [
                now()->toDateString(),
                now()->addDays(30)->toDateString()
            ]);
        } elseif ($this->filterExpiry === 'safe') {
            $query->where(function ($q) {
                $q->where('tgl_kadaluarsa', '>', now()->addDays(30)->toDateString())
                    ->orWhereNull('tgl_kadaluarsa');
            });
        }

        return [
            'stoks' => $query->latest('tgl_masuk')->paginate(15),
            'barangs' => Barang::orderBy('nama_barang')->get(['id', 'kode_barang', 'nama_barang']),
        ];
    }

    public function adjustStok($id, $jumlah, $reason = '')
    {
        // TODO: implement adjustment modal
    }
};
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-800">Stok per Batch</h3>
            <p class="text-sm text-slate-500">Kelola stok barang berdasarkan batch penerimaan</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative flex-1 md:w-72">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all" placeholder="Cari batch / barang...">
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 mb-4">
        <select wire:model.live="filterBarang" class="px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm bg-white">
            <option value="">Semua Barang</option>
            @foreach($barangs as $barang)
                <option value="{{ $barang->id }}">{{ $barang->kode_barang }} - {{ $barang->nama_barang }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterExpiry" class="px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm bg-white">
            <option value="">Semua Status Expiry</option>
            <option value="expired">Kadaluarsa</option>
            <option value="soon">Akan Kadaluarsa (30 hari)</option>
            <option value="safe">Aman</option>
        </select>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Batch</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Barang</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Stok</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Tgl. Kadaluarsa</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Tgl. Masuk</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Harga Beli</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">No. Terima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stoks as $stok)
                    @php
                        $expiryDays = $stok->tgl_kadaluarsa ? (int) now()->diffInDays($stok->tgl_kadaluarsa, false) : null;
                        $expiryBadge = match(true) {
                            $expiryDays === null => 'bg-slate-100 text-slate-600',
                            $expiryDays < 0 => 'bg-red-100 text-red-700',
                            $expiryDays <= 7 => 'bg-orange-100 text-orange-700',
                            $expiryDays <= 30 => 'bg-amber-100 text-amber-700',
                            default => 'bg-emerald-100 text-emerald-700',
                        };
                        $expiryLabel = match(true) {
                            $expiryDays === null => '-',
                            $expiryDays < 0 => 'Kadaluarsa',
                            $expiryDays == 0 => 'Hari Ini',
                            $expiryDays <= 30 => $expiryDays . ' hari',
                            default => $expiryDays . ' hari',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm font-bold text-blue-600">{{ $stok->batch_number ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-slate-800">{{ $stok->barang->nama_barang ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400 uppercase font-bold">{{ $stok->barang->kode_barang ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-black text-slate-800">{{ number_format($stok->stok, 0) }}</span>
                            <span class="text-xs text-slate-400 ml-1">{{ $stok->barang->satuan ?? '' }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($stok->tgl_kadaluarsa)
                                <span class="text-xs font-bold">{{ $stok->tgl_kadaluarsa->format('d/m/Y') }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $expiryBadge }} ml-1">{{ $expiryLabel }}</span>
                            @else
                                <span class="text-xs text-slate-400 italic">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-slate-600">
                            {{ $stok->tgl_masuk?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-mono text-slate-600">
                            Rp {{ number_format($stok->harga_beli ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($stok->penerimaanBarang)
                                <a href="{{ route('penerimaan.show', $stok->penerimaanBarang) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 underline">
                                    {{ $stok->penerimaanBarang->no_terima }}
                                </a>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <p class="text-slate-400 font-medium text-sm">Tidak ada data batch stok</p>
                                <p class="text-slate-300 text-xs mt-1">Batch stok akan muncul setelah penerimaan diverifikasi</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $stoks->links() }}
        </div>
    </div>
</div>
