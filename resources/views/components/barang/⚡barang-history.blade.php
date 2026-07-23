<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StockMovement;
use App\Models\Barang;

new class extends Component {
    use WithPagination;

    public $barangId;

    public function mount($barangId = null)
    {
        $this->barangId = $barangId;
    }

    public function with()
    {
        $query = StockMovement::with(['user', 'barang', 'reference', 'barangStok']);
        
        if ($this->barangId) {
            $query->where('barang_id', $this->barangId);
        }

        return [
            'movements' => $query->latest()->paginate(15),
            'barang' => $this->barangId ? Barang::withoutGlobalScope('active')->find($this->barangId) : null,
        ];
    }
};
?>

<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <a href="{{ route('barang.index') }}" class="text-blue-600 hover:text-blue-700 font-medium flex items-center transition-colors mb-2">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar Barang
            </a>
            <h3 class="text-xl font-bold text-slate-800">Log Pergerakan Stok</h3>
            @if($barang)
                <p class="text-sm text-slate-500 italic">{{ $barang->kode_barang }} - {{ $barang->nama_barang }}</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Waktu</th>
                        @if(!$barangId)
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Barang</th>
                        @endif
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Jumlah</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Sebelum</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Sesudah</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Batch</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $movement)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-xs text-slate-500 font-medium">
                            {{ $movement->created_at->format('d/m/Y H:i') }}
                        </td>
                        @if(!$barangId)
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-slate-800">{{ $movement->barang->nama_barang }}</div>
                            <div class="text-[10px] text-slate-400 uppercase font-bold">{{ $movement->barang->kode_barang }}</div>
                        </td>
                        @endif
                        <td class="px-6 py-4">
                            @php
                                $badgeClass = match($movement->type) {
                                    'in' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'out' => 'bg-rose-100 text-rose-700 border-rose-200',
                                    'adjustment' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                                $typeLabel = match($movement->type) {
                                    'in' => 'Masuk',
                                    'out' => 'Keluar',
                                    'adjustment' => 'Penyesuaian',
                                    default => ucfirst($movement->type)
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase {{ $badgeClass }}">
                                {{ $typeLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-black {{ $movement->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ ($movement->quantity > 0 ? '+' : '') . $movement->quantity }}
                        </td>
                        <td class="px-6 py-4 text-center text-slate-400 font-mono text-sm">
                            {{ $movement->before_quantity }}
                        </td>
                        <td class="px-6 py-4 text-center text-slate-900 font-bold font-mono text-sm">
                            {{ $movement->after_quantity }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-600">
                            @if($movement->barangStok && $movement->barangStok->batch_number)
                                <span class="font-mono font-bold">{{ $movement->barangStok->batch_number }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $movement->reason ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">
                            {{ $movement->user->name ?? 'System' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $barangId ? 8 : 9 }}" class="px-6 py-10 text-center">
                            <p class="text-slate-400 italic text-sm">Belum ada riwayat pergerakan stok.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
            {{ $movements->links() }}
        </div>
    </div>
</div>
