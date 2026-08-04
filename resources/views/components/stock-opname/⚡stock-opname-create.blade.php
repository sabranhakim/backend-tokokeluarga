<?php

use Livewire\Component;
use App\Models\Barang;
use App\Services\StockOpnameService;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    public $tgl_opname;
    public $keterangan;

    public $items = [];

    public function mount()
    {
        if (! Gate::allows('create stock_opname') && ! auth()->user()->hasAnyRole(['admin', 'staff'])) {
            session()->flash('error', 'Anda tidak memiliki hak akses untuk membuat stock opname.');
            return $this->redirect(route('dashboard'), navigate: true);
        }
        $this->tgl_opname = date('Y-m-d');

        foreach (Barang::orderBy('nama_barang')->get() as $barang) {
            $this->items[] = [
                'barang_id' => $barang->id,
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->nama_barang,
                'satuan' => $barang->satuan,
                'stok_sistem' => (int) $barang->stok,
                'stok_fisik' => (int) $barang->stok,
            ];
        }
    }

    public function save(StockOpnameService $service)
    {
        if (! Gate::allows('create stock_opname') && ! auth()->user()->hasAnyRole(['admin', 'staff'])) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menyimpan data ini.');
            return;
        }

        try {
            $service->store([
                'tgl_opname' => $this->tgl_opname,
                'keterangan' => $this->keterangan,
                'items' => $this->items,
            ]);

            session()->flash('success', 'Stock opname berhasil disimpan sebagai draft.');
            return redirect()->route('stock-opname.index');
        } catch (\Exception $e) {
            $this->addError('general', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
};
?>

<div class="p-6">
    <div class="mb-6">
        <a href="{{ route('stock-opname.index') }}" class="text-blue-600 hover:text-blue-700 font-medium flex items-center transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar
        </a>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-50 pb-2">Informasi Stock Opname</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Tanggal Opname</label>
                            <div class="relative group"
                                 wire:ignore
                                 x-data="{
                                    value: @entangle('tgl_opname'),
                                    instance: null,
                                    init() {
                                        this.instance = flatpickr($refs.input, {
                                            dateFormat: 'Y-m-d',
                                            locale: 'id',
                                            onChange: (selectedDates, dateStr) => {
                                                this.value = dateStr;
                                            }
                                        });
                                        this.$watch('value', (val) => {
                                            if (this.instance.currentDateStr !== val) {
                                                this.instance.setDate(val);
                                            }
                                        });
                                    }
                                 }">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <input x-ref="input"
                                       type="text"
                                       readonly
                                       class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white cursor-pointer @error('tgl_opname') border-red-500 @enderror"
                                       placeholder="Pilih Tanggal">
                            </div>
                            @error('tgl_opname') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Keterangan (Opsional)</label>
                            <textarea wire:model="keterangan" rows="3" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all resize-none" placeholder="Catatan pelaksanaan opname..."></textarea>
                        </div>
                    </div>

                    <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start">
                        <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <p class="text-sm font-medium text-amber-700">Data disimpan sebagai draft. Penyesuaian stok dilakukan setelah klik "Terapkan".</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden"
                     x-data="{ search: '' }">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-800">Daftar Stok Fisik</h3>
                        <div class="flex items-center gap-3">
                            <input x-model="search"
                                   type="text"
                                   class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                                   placeholder="Cari barang...">
                            <span class="text-xs font-bold text-slate-500 px-2 py-1 bg-slate-100 rounded-lg">{{ count($items) }} barang</span>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto rounded-xl border border-slate-100">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-slate-50/60 text-slate-400 text-[10px] font-black uppercase tracking-wider border-b border-slate-100">
                                        <th class="px-4 py-3">Kode</th>
                                        <th class="px-4 py-3">Nama Barang</th>
                                        <th class="px-4 py-3 text-right">Stok Sistem</th>
                                        <th class="px-4 py-3 text-right">Stok Fisik</th>
                                        <th class="px-4 py-3 text-right">Selisih</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($items as $index => $item)
                                    <tr wire:key="opname-{{ $index }}"
                                        x-show="search === '' || '{{ $item['kode_barang'] }} {{ $item['nama_barang'] }}'.toLowerCase().includes(search.toLowerCase())">
                                        <td class="px-4 py-2.5 font-mono text-[11px] font-bold text-slate-500">{{ $item['kode_barang'] }}</td>
                                        <td class="px-4 py-2.5 text-sm font-medium text-slate-800">{{ $item['nama_barang'] }} <span class="text-xs text-slate-400">({{ $item['satuan'] }})</span></td>
                                        <td class="px-4 py-2.5 text-right text-sm text-slate-600">{{ $item['stok_sistem'] }}</td>
                                        <td class="px-4 py-2.5 text-right">
                                            <input type="number" min="0" wire:model="items.{{ $index }}.stok_fisik"
                                                   class="w-24 text-right px-3 py-1.5 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm">
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            @php $selisih = $item['stok_fisik'] - $item['stok_sistem']; @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $selisih == 0 ? 'bg-slate-100 text-slate-500' : ($selisih > 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600') }}">
                                                {{ $selisih > 0 ? '+' : '' }}{{ $selisih }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-3 rounded-xl font-bold shadow-lg shadow-blue-200 transition-all active:scale-95 flex items-center disabled:opacity-70 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="save" class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                Simpan Stock Opname
                            </span>
                            <span wire:loading wire:target="save" class="flex items-center">
                                <svg class="animate-spin h-5 w-5 mr-3 text-white flex justify-center" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
