<?php

use Livewire\Component;
use App\Models\Barang;
use App\Models\PenerimaanBarang;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public $activeTab = 'stok_menipis';

    // Filters
    public $startDate;
    public $endDate;
    public $expiredInDays = 30;
    public $selectedSupplierId = '';

    public function mount()
    {
        if (!auth()->user()->can('manage laporan')) {
            session()->flash('error', 'Anda tidak memiliki akses ke halaman laporan.');
            return $this->redirect(route('dashboard'), navigate: true);
        }

        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }


    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function getLowStockDataProperty()
    {
        return Barang::with('kategori')
            ->whereColumn('stok', '<=', 'stok_minimal')
            ->orderBy('stok', 'asc')
            ->get();
    }

    public function getExpiredDataProperty()
    {
        $query = Barang::with('kategori')
            ->whereNotNull('tgl_kadaluarsa');

        if ($this->expiredInDays == 'already_expired') {
            $query->where('tgl_kadaluarsa', '<', now()->toDateString());
        } else {
            $query->whereBetween('tgl_kadaluarsa', [
                now()->toDateString(),
                now()->addDays((int)$this->expiredInDays)->toDateString()
            ]);
        }

        return $query->orderBy('tgl_kadaluarsa', 'asc')->get();
    }

    public function getPenerimaanPeriodeDataProperty()
    {
        return PenerimaanBarang::with(['supplier', 'detailPenerimaans'])
            ->whereBetween('tgl_terima', [$this->startDate, $this->endDate])
            ->latest('tgl_terima')
            ->get();
    }

    public function getPenerimaanSupplierDataProperty()
    {
        if (!$this->selectedSupplierId) {
            return collect();
        }

        return PenerimaanBarang::with(['detailPenerimaans.barang'])
            ->where('supplier_id', $this->selectedSupplierId)
            ->latest('tgl_terima')
            ->get();
    }

    public function with()
    {
        return [
            'suppliers' => Supplier::orderBy('nama_supplier')->get()
        ];
    }
};
?>

<div class="p-8">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-slate-800">Laporan</h3>
    </div>
    <!-- Tab Navigation -->
    <div class="flex space-x-1 bg-slate-100 p-1 rounded-xl mb-8 w-fit">
        <button wire:click="setTab('stok_menipis')"
            class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeTab === 'stok_menipis' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Stok Menipis
        </button>
        <button wire:click="setTab('kadaluarsa')"
            class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeTab === 'kadaluarsa' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Barang Kadaluarsa
        </button>
        <button wire:click="setTab('penerimaan_periode')"
            class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeTab === 'penerimaan_periode' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Penerimaan Periode
        </button>
        <button wire:click="setTab('penerimaan_supplier')"
            class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeTab === 'penerimaan_supplier' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Per Supplier
        </button>
    </div>

    <!-- Filters Section -->
    <div class="mb-6 flex flex-wrap gap-4 items-end bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        @if($activeTab === 'kadaluarsa')
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Kurun Waktu</label>
                <select wire:model.live="expiredInDays" class="bg-slate-50 border-none rounded-lg text-sm font-medium focus:ring-2 focus:ring-blue-500">
                    <option value="7">7 Hari Ke Depan</option>
                    <option value="30">30 Hari Ke Depan</option>
                    <option value="90">90 Hari Ke Depan</option>
                    <option value="already_expired">Sudah Kadaluarsa</option>
                </select>
            </div>
        @endif

        @if($activeTab === 'penerimaan_periode')
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Mulai</label>
                <input type="date" wire:model.live="startDate" class="bg-slate-50 border-none rounded-lg text-sm font-medium focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Selesai</label>
                <input type="date" wire:model.live="endDate" class="bg-slate-50 border-none rounded-lg text-sm font-medium focus:ring-2 focus:ring-blue-500">
            </div>
        @endif

        @if($activeTab === 'penerimaan_supplier')
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Pilih Supplier</label>
                <select wire:model.live="selectedSupplierId" class="bg-slate-50 border-none rounded-lg text-sm font-medium focus:ring-2 focus:ring-blue-500 min-w-[200px]">
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="ml-auto flex gap-3">
            @if($activeTab === 'stok_menipis')
                <a href="{{ route('laporan.export.stok') }}" class="flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </a>
            @elseif($activeTab === 'kadaluarsa')
                <a href="{{ route('laporan.export.kadaluarsa', ['days' => $expiredInDays]) }}" class="flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </a>
            @elseif($activeTab === 'penerimaan_periode')
                <a href="{{ route('laporan.export.penerimaan-periode', ['start' => $startDate, 'end' => $endDate]) }}" class="flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </a>
            @elseif($activeTab === 'penerimaan_supplier')
                @if($selectedSupplierId)
                    <a href="{{ route('laporan.export.penerimaan-supplier', ['supplier_id' => $selectedSupplierId]) }}" class="flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export Excel
                    </a>
                @endif
            @endif
            
        </div>
    </div>

    <!-- Data Tables -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        @if($activeTab === 'stok_menipis')
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Barang</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Kategori</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Stok Saat Ini</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Batas Minimal</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($this->lowStockData as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $item->nama_barang }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $item->kode_barang }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $item->kategori->nama_kategori ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                    {{ $item->stok }} {{ $item->satuan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-slate-500 font-bold">{{ $item->stok_minimal }}</td>
                            <td class="px-6 py-4 text-xs font-bold text-red-500 uppercase tracking-tighter italic">Segera Re-stock</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-slate-400 italic">Semua stok dalam kondisi aman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        @if($activeTab === 'kadaluarsa')
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Barang</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Kategori</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Tgl Kadaluarsa</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Sisa Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($this->expiredData as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $item->nama_barang }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $item->kategori->nama_kategori ?? '-' }}</td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-slate-700">
                                {{ $item->tgl_kadaluarsa->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $days = (int) now()->diffInDays($item->tgl_kadaluarsa, false);
                                    $color = $days < 0 ? 'text-red-600' : ($days < 7 ? 'text-amber-600' : 'text-slate-600');
                                @endphp
                                <span class="text-sm font-bold {{ $color }}">
                                    @if($days < 0)
                                        Sudah Kadaluarsa ({{ abs($days) }} hari lalu)
                                    @elseif($days == 0)
                                        Kadaluarsa HARI INI
                                    @else
                                        {{ $days }} Hari Lagi
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-slate-400 italic">Tidak ada data barang sesuai kriteria.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        @if($activeTab === 'penerimaan_periode')
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">No. Penerimaan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tgl Terima</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Supplier</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Jumlah Item</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($this->penerimaanPeriodeData as $row)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-sm text-blue-600 underline">{{ $row->no_terima }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $row->tgl_terima->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ $row->supplier->nama_supplier }}</td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-slate-700">{{ $row->detailPenerimaans->count() }} Item</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase {{ $row->status_verifikasi === 'verified' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ ucfirst($row->status_verifikasi) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-slate-400 italic">Tidak ada data penerimaan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        @if($activeTab === 'penerimaan_supplier')
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">No. Penerimaan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tgl Terima</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Detail Barang</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($this->penerimaanSupplierData as $row)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-sm text-blue-600">{{ $row->no_terima }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $row->tgl_terima->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-800">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($row->detailPenerimaans as $detail)
                                        <li>{{ $detail->barang->nama_barang }} ({{ $detail->jumlah_terima }} {{ $detail->barang->satuan }})</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase {{ $row->status_verifikasi === 'verified' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ ucfirst($row->status_verifikasi) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-slate-400 italic">
                            {{ $selectedSupplierId ? 'Tidak ada riwayat penerimaan untuk supplier ini.' : 'Silakan pilih supplier untuk melihat laporan.' }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
</div>
