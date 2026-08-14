<?php

use Livewire\Component;
use App\Models\Barang;
use App\Models\BarangStok;
use App\Models\BarangKeluar;
use App\Models\StockMovement;
use App\Models\PenerimaanBarang;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new class extends Component {
    public $activeTab = 'stok_menipis';

    // Filters
    public $startDate;
    public $endDate;
    public $expiredInDays = 30;
    public $selectedSupplierId = '';
    public $selectedMonth;
    public $selectedYear;
    public $mutasiType = '';

    public function mount()
    {
        if (!auth()->user()->can('manage laporan')) {
            session()->flash('error', 'Anda tidak memiliki akses ke halaman laporan.');
            return $this->redirect(route('dashboard'), navigate: true);
        }

        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }


    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function getLowStockDataProperty()
    {
        return Barang::with(['kategori', 'supplier'])
            ->whereColumn('stok', '<=', 'stok_minimal')
            ->orderBy('stok', 'asc')
            ->get();
    }

    public function getExpiredDataProperty()
    {
        $query = BarangStok::with('barang.kategori')
            ->whereNotNull('tgl_kadaluarsa')
            ->where('stok', '>', 0);

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
            ->whereMonth('tgl_terima', $this->selectedMonth)
            ->whereYear('tgl_terima', $this->selectedYear)
            ->latest('tgl_terima')
            ->get();
    }

    public function getBarangKeluarDataProperty()
    {
        return BarangKeluar::with(['user', 'detailBarangKeluars.barang'])
            ->whereBetween('tgl_keluar', [$this->startDate, $this->endDate])
            ->latest('tgl_keluar')
            ->get();
    }

    public function getMutasiStokDataProperty()
    {
        return StockMovement::with(['barang', 'user'])
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->when($this->mutasiType, fn($q) => $q->where('type', $this->mutasiType))
            ->latest('created_at')
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
        <button wire:click="setTab('barang_keluar')"
            class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeTab === 'barang_keluar' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Barang Keluar
        </button>
        <button wire:click="setTab('mutasi_stok')"
            class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeTab === 'mutasi_stok' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Mutasi Stok
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

        @if($activeTab === 'penerimaan_periode' || $activeTab === 'barang_keluar' || $activeTab === 'mutasi_stok')
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Mulai</label>
                <input type="date" wire:model.live="startDate" class="bg-slate-50 border-none rounded-lg text-sm font-medium focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Selesai</label>
                <input type="date" wire:model.live="endDate" class="bg-slate-50 border-none rounded-lg text-sm font-medium focus:ring-2 focus:ring-blue-500">
            </div>
        @endif

        @if($activeTab === 'mutasi_stok')
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Mutasi</label>
                <select wire:model.live="mutasiType" class="bg-slate-50 border-none rounded-lg text-sm font-medium focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="in">Masuk</option>
                    <option value="out">Keluar</option>
                </select>
            </div>
        @endif

        @if($activeTab === 'penerimaan_supplier')
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Pilih Supplier</label>
                <select wire:model.live="selectedSupplierId" class="bg-slate-50 border-none rounded-lg text-sm font-medium focus:ring-2 focus:ring-blue-500 min-w-[200px]">
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->getKey() }}">{{ $supplier->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Bulan</label>
                <select wire:model.live="selectedMonth" class="bg-slate-50 border-none rounded-lg text-sm font-medium focus:ring-2 focus:ring-blue-500">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}">{{ Carbon::create()->month($m)->translatedFormat('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Tahun</label>
                <select wire:model.live="selectedYear" class="bg-slate-50 border-none rounded-lg text-sm font-medium focus:ring-2 focus:ring-blue-500">
                    @foreach(range(now()->year, now()->year - 5) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
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
                    <a href="{{ route('laporan.export.penerimaan-supplier', ['supplier_id' => $selectedSupplierId, 'month' => $selectedMonth, 'year' => $selectedYear]) }}" class="flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export Excel
                    </a>
                @endif
            @elseif($activeTab === 'barang_keluar')
                <a href="{{ route('laporan.export.barang-keluar', ['start' => $startDate, 'end' => $endDate]) }}" class="flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </a>
            @elseif($activeTab === 'mutasi_stok')
                <a href="{{ route('laporan.export.mutasi-stok', ['start' => $startDate, 'end' => $endDate, 'type' => $mutasiType]) }}" class="flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </a>
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
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Supplier</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Stok Saat Ini</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Batas Minimal</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Aksi</th>
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
                            <td class="px-6 py-4 text-sm text-slate-600">
                                @if($item->supplier)
                                    <div class="font-medium text-slate-800">{{ $item->supplier->nama_supplier }}</div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $item->supplier->no_telp ?? '-' }}</div>
                                @else
                                    <span class="text-slate-400 italic">Belum ditentukan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                    {{ $item->stok }} {{ $item->satuan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-slate-500 font-bold">{{ $item->stok_minimal }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($item->supplier && $item->supplier->no_telp)
                                    @php
                                        $phone = $item->supplier->no_telp;
                                        if (str_starts_with($phone, '0')) {
                                            $phone = '62' . substr($phone, 1);
                                        }
                                        $message = "Halo " . $item->supplier->nama_supplier . ", saya " . auth()->user()->name . " dari Grosir Toko Keluarga. Ingin menginfokan bahwa stok *" . $item->nama_barang . "* kami saat ini menipis (sisa *" . $item->stok . " " . $item->satuan . "*). Mohon info untuk pengiriman kembali. Terima kasih.";
                                        $waUrl = "https://wa.me/" . $phone . "?text=" . urlencode($message);
                                    @endphp
                                    <a href="{{ $waUrl }}" target="_blank" class="inline-flex items-center px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold hover:bg-emerald-200 transition-all border border-emerald-200">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.319 1.592 5.548 0 10.058-4.51 10.06-10.059.002-2.689-1.047-5.215-2.951-7.121-1.905-1.904-4.432-2.951-7.125-2.952-5.548 0-10.06 4.511-10.062 10.06-.001 2.12.549 4.156 1.596 5.945l-.998 3.646 3.161-.83zm10.742-7.135c-.131-.218-.48-.349-.99-.611-.508-.262-3.011-1.486-3.478-1.661-.467-.175-.808-.262-1.149.262-.34.524-1.315 1.661-1.611 2-.297.34-.593.383-1.102.12-.51-.262-2.15-.792-4.1-2.62-1.517-1.353-2.54-3.024-2.837-3.548-.297-.524-.031-.808.23-1.068.234-.233.51-.59.765-.886.256-.296.34-.508.51-.848.17-.339.085-.634-.042-.896-.128-.262-1.149-2.766-1.574-3.792-.413-1.002-.835-.866-1.149-.882-.296-.016-.638-.016-.978-.016s-.893.128-1.36.611c-.468.481-1.786 1.748-1.786 4.26s1.83 4.956 2.085 5.285c.255.33 3.6 5.501 8.72 7.71 1.218.525 2.17.84 2.91 1.075 1.226.39 2.342.335 3.224.203.984-.148 3.012-1.23 3.436-2.417.425-1.188.425-2.203.297-2.416z"/></svg>
                                        WhatsApp
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">Semua stok dalam kondisi aman.</td></tr>
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
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $item->barang->nama_barang }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $item->barang->kategori->nama_kategori ?? '-' }}</td>
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
                                        <li>{{ $detail->barang->nama_barang }} ({{ $detail->jumlah }} {{ $detail->barang->satuan }})</li>
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

        @if($activeTab === 'barang_keluar')
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">No. Keluar</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tgl Keluar</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Jenis</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Petugas</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Detail Barang</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Total Item</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($this->barangKeluarData as $row)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-sm text-blue-600">{{ $row->no_keluar }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $row->tgl_keluar->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase bg-slate-100 text-slate-700">{{ $row->jenis_keluar_label }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $row->user->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-800">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($row->detailBarangKeluars as $detail)
                                        <li>{{ $detail->barang->nama_barang }} ({{ $detail->jumlah }} {{ $detail->barang->satuan }})</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-slate-700">{{ $row->detailBarangKeluars->count() }} Item</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">Tidak ada data barang keluar pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        @if($activeTab === 'mutasi_stok')
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tanggal</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Barang</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tipe</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Jumlah</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Stok Sebelum</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Stok Setelah</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Alasan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($this->mutasiStokData as $row)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $row->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 font-bold text-sm text-slate-800">{{ $row->barang->nama_barang ?? 'Barang tidak diketahui' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase {{ $row->type === 'in' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $row->type === 'in' ? 'Masuk' : 'Keluar' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-slate-700">{{ $row->quantity }}</td>
                            <td class="px-6 py-4 text-center text-sm text-slate-500">{{ $row->before_quantity }}</td>
                            <td class="px-6 py-4 text-center text-sm text-slate-500">{{ $row->after_quantity }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $row->reason ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $row->user->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-10 text-center text-slate-400 italic">Tidak ada mutasi stok pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
</div>
