<?php

use Livewire\Component;
use App\Models\Barang;
use App\Models\StockMovement;

new class extends Component {
    public $barangId;

    public function mount($barangId)
    {
        $this->barangId = $barangId;
    }

    public function with()
    {
        $barang = Barang::withoutGlobalScope('active')->with('kategori')->findOrFail($this->barangId);
        
        $recentMovements = StockMovement::where('barang_id', $this->barangId)
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'total_in' => StockMovement::where('barang_id', $this->barangId)->where('type', 'in')->sum('quantity'),
            'total_out' => abs(StockMovement::where('barang_id', $this->barangId)->where('type', 'out')->sum('quantity')),
            'last_movement' => StockMovement::where('barang_id', $this->barangId)->latest()->first(),
        ];

        return [
            'barang' => $barang,
            'recentMovements' => $recentMovements,
            'stats' => $stats,
        ];
    }
};
?>

<div class="p-6">
    <div class="mb-6">
        <a href="{{ route('barang.index') }}" class="text-blue-600 hover:text-blue-700 font-medium flex items-center transition-colors mb-2">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Barang
        </a>
        <h3 class="text-2xl font-bold text-slate-800">Detail Stok Barang</h3>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info Card -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h4 class="font-bold text-slate-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Informasi Dasar
                    </h4>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Kode Barang</p>
                        <p class="text-lg font-mono font-bold text-slate-800">{{ $barang->kode_barang }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Kategori</p>
                        <p class="text-lg font-medium text-slate-800">{{ $barang->kategori->nama_kategori ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs font-bold text-slate-400 uppercase">Nama Barang</p>
                        <p class="text-xl font-bold text-slate-800">{{ $barang->nama_barang }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Satuan</p>
                        <p class="text-slate-800 font-medium">{{ $barang->satuan }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Tanggal Kadaluarsa</p>
                        <p class="text-slate-800 font-medium">{{ $barang->tgl_kadaluarsa ? $barang->tgl_kadaluarsa->format('d/m/Y') : 'Tidak ada' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Harga Beli</p>
                        <p class="text-lg font-mono font-bold text-slate-600">Rp {{ number_format($barang->harga_beli, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Harga Jual</p>
                        <p class="text-lg font-mono font-bold text-blue-600">Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Movements -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h4 class="font-bold text-slate-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pergerakan Terakhir
                    </h4>
                    <a href="{{ route('barang.history', $barang->id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 uppercase tracking-wider">
                        Lihat Semua
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/30">
                                <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Waktu</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tipe</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Jumlah</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentMovements as $movement)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    {{ $movement->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $typeLabel = match($movement->type) {
                                            'in' => 'Masuk',
                                            'out' => 'Keluar',
                                            'adjustment' => 'Penyesuaian',
                                            default => ucfirst($movement->type)
                                        };
                                        $typeColor = match($movement->type) {
                                            'in' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                            'out' => 'text-rose-600 bg-rose-50 border-rose-100',
                                            'adjustment' => 'text-amber-600 bg-amber-50 border-amber-100',
                                            default => 'text-slate-600 bg-slate-50 border-slate-100'
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold border uppercase {{ $typeColor }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold {{ $movement->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ ($movement->quantity > 0 ? '+' : '') . $movement->quantity }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    {{ $movement->reason ?? '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-400 italic text-sm">
                                    Belum ada data pergerakan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar / Stats Card -->
        <div class="space-y-6">
            <!-- Stock Status -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 text-center">
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-2">Stok Saat Ini</p>
                <div class="flex items-center justify-center gap-2 mb-2">
                    <span class="text-5xl font-black text-slate-800">{{ $barang->stok }}</span>
                    <span class="text-lg font-bold text-slate-400 uppercase">{{ $barang->satuan }}</span>
                </div>
                
                @php
                    $isLow = $barang->stok <= $barang->stok_minimal;
                @endphp
                
                @if($isLow)
                    <div class="bg-rose-50 border border-rose-100 text-rose-700 px-4 py-2 rounded-lg text-xs font-bold flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        STOK DIBAWAH MINIMAL ({{ $barang->stok_minimal }})
                    </div>
                @else
                    <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-2 rounded-lg text-xs font-bold flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        STOK AMAN
                    </div>
                @endif

                <div class="mt-6 pt-6 border-t border-slate-50 grid grid-cols-2 gap-4">
                    <div class="text-left">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Total Masuk</p>
                        <p class="text-lg font-bold text-emerald-600">{{ $stats['total_in'] }}</p>
                    </div>
                    <div class="text-left">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Total Keluar</p>
                        <p class="text-lg font-bold text-rose-600">{{ $stats['total_out'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Action / Status -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h4 class="font-bold text-slate-800 mb-4">Status & Pengaturan</h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Status Aktif</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $barang->is_active ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $barang->is_active ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Batas Minimal</span>
                        <span class="text-sm font-bold text-slate-800">{{ $barang->stok_minimal }} {{ $barang->satuan }}</span>
                    </div>
                    <div class="pt-4 border-t border-slate-50">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Update Terakhir</p>
                        <p class="text-xs text-slate-600">
                            {{ $barang->updated_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
