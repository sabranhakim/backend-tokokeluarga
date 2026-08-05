<?php

use Livewire\Component;
use App\Models\Barang;
use App\Models\BarangStok;
use App\Services\BarangKeluarService;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    public $tgl_keluar;
    public $jenis_keluar;
    public $keterangan;

    public $items = [];
    public $showPreview = false;

    protected $rules = [
        'tgl_keluar' => 'required|date',
        'jenis_keluar' => 'required|in:penjualan,kerusakan,kadaluarsa,pemakaian_internal',
        'keterangan' => 'nullable|string|max:1000',
        'items' => 'required|array|min:1',
        'items.*.barang_id' => 'required|exists:barangs,id|distinct',
        'items.*.jumlah' => 'required|numeric|min:1',
    ];

    protected $messages = [
        'items.*.barang_id.distinct' => 'Barang yang sama tidak boleh ditambahkan lebih dari satu kali dalam satu transaksi.',
    ];

    public function mount()
    {
        if (!Gate::allows('create barang_keluar') && !auth()->user()->hasAnyRole(['admin', 'staff'])) {
            session()->flash('error', 'Anda tidak memiliki hak akses untuk menambah barang keluar.');
            return $this->redirect(route('dashboard'), navigate: true);
        }
        $this->tgl_keluar = date('Y-m-d');
        $this->jenis_keluar = 'penjualan';
        $this->addItem();
    }

    public function addItem()
    {
        $this->items[] = [
            'barang_id' => '',
            'jumlah' => 1,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function showConfirmPreview()
    {
        $this->validate();
        $this->showPreview = true;
    }

    public function closePreview()
    {
        $this->showPreview = false;
    }

    public function save(BarangKeluarService $service)
    {
        if (!Gate::allows('create barang_keluar') && !auth()->user()->hasAnyRole(['admin', 'staff'])) {
            $this->dispatch('notify', 'Anda tidak memiliki hak akses untuk menyimpan data ini.');
            return;
        }
        $validated = $this->validate();

        try {
            $service->store($validated);

            session()->flash('success', 'Barang keluar berhasil disimpan dan stok telah diperbarui.');
            return redirect()->route('barang-keluar.index');
        } catch (\Exception $e) {
            $this->showPreview = false;
            $this->addError('general', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function with()
    {
        return [
            'barangs' => Barang::all(),
        ];
    }

    public function getStokTersedia($barangId)
    {
        if (!$barangId) return 0;
        $barang = Barang::find($barangId);
        return $barang ? $barang->stok : 0;
    }
};
?>

<div class="p-6">
    <div class="mb-6">
        <a href="{{ route('barang-keluar.index') }}" class="text-blue-600 hover:text-blue-700 font-medium flex items-center transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar
        </a>
    </div>

    <form wire:submit.prevent="showConfirmPreview" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-50 pb-2">Informasi Barang Keluar</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Tanggal Keluar</label>
                            <div class="relative group"
                                 wire:ignore
                                 x-data="{
                                    value: @entangle('tgl_keluar'),
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
                                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input x-ref="input"
                                       type="text"
                                       readonly
                                       class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white cursor-pointer @error('tgl_keluar') border-red-500 @enderror"
                                       placeholder="Pilih Tanggal">
                            </div>
                            @error('tgl_keluar') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Jenis Keluar</label>
                            <select wire:model="jenis_keluar" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white cursor-pointer @error('jenis_keluar') border-red-500 @enderror">
                                <option value="penjualan">Penjualan</option>
                                <option value="kerusakan">Kerusakan</option>
                                <option value="kadaluarsa">Kadaluarsa</option>
                                <option value="pemakaian_internal">Pemakaian Internal</option>
                            </select>
                            @error('jenis_keluar') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Keterangan (Opsional)</label>
                            <textarea wire:model="keterangan" rows="3" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all resize-none" placeholder="Catatan keperluan barang keluar..."></textarea>
                            @error('keterangan') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-800">Daftar Barang</h3>
                        <button type="button" wire:click="addItem" class="text-sm bg-blue-50 text-blue-600 px-3 py-1 rounded-lg font-bold hover:bg-blue-100 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Tambah Baris
                        </button>
                    </div>

                    <div class="p-6">
                        <div class="space-y-4">
                            @php $barangsJson = $barangs->map(fn($b) => ['id' => $b->id, 'kode_barang' => $b->kode_barang, 'nama_barang' => $b->nama_barang, 'satuan' => $b->satuan, 'stok' => $b->stok])->toJson(); @endphp
                            @foreach($items as $index => $item)
                            <div wire:key="item-{{ $index }}" class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 relative group transition-all hover:bg-slate-50">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                                <div class="md:col-span-11">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Cari SKU / Nama Barang</label>
                                    <div wire:key="search-{{ $index }}" x-data="{
                                        search: '',
                                        selectedId: @entangle('items.' . $index . '.barang_id'),
                                        selectedLabel: '',
                                        open: false,
                                        barangs: {{ $barangsJson }},
                                        get filtered() {
                                            if (!this.search.trim()) return [];
                                            const q = this.search.toLowerCase();
                                            return this.barangs.filter(b =>
                                                b.kode_barang.toLowerCase().includes(q) ||
                                                b.nama_barang.toLowerCase().includes(q)
                                            ).slice(0, 20);
                                        },
                                        select(barang) {
                                            this.selectedId = barang.id;
                                            this.selectedLabel = barang.kode_barang + ' - ' + barang.nama_barang + ' (Stok: ' + barang.stok + ')';
                                            this.search = '';
                                            this.open = false;
                                        },
                                        init() {
                                            if (this.selectedId) {
                                                const found = this.barangs.find(b => b.id === this.selectedId);
                                                if (found) this.selectedLabel = found.kode_barang + ' - ' + found.nama_barang + ' (Stok: ' + found.stok + ')';
                                            }
                                        }
                                    }" class="relative">
                                        <div class="relative">
                                            <input x-model="search"
                                                   @click="open = true"
                                                   @click.outside="open = false"
                                                   @input="open = true"
                                                   type="text"
                                                   :placeholder="selectedId ? '' : 'Ketik SKU atau nama barang...'"
                                                   class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm">
                                            <template x-if="selectedId && !search">
                                                <div class="absolute inset-0 flex items-center px-4 text-sm font-bold text-slate-600 pointer-events-none" x-text="selectedLabel"></div>
                                            </template>
                                        </div>
                                        <div x-show="open && search.trim() && filtered.length > 0"
                                             x-cloak
                                             class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                                            <template x-for="barang in filtered" :key="barang.id">
                                                <div @click="select(barang)"
                                                      class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors">
                                                    <div class="text-sm font-bold text-slate-800" x-text="barang.kode_barang"></div>
                                                    <div class="text-xs text-slate-500" x-text="barang.nama_barang + ' (' + barang.satuan + ') - Stok: ' + barang.stok"></div>
                                                </div>
                                            </template>
                                        </div>
                                        <div x-show="open && search.trim() && filtered.length === 0"
                                             x-cloak
                                             class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg p-4 text-center text-sm text-slate-400">
                                            Barang tidak ditemukan
                                        </div>
                                    </div>
                                    @error("items.$index.barang_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-1 flex justify-end md:justify-start">
                                    @if(count($items) > 1)
                                    <button type="button" wire:click="removeItem({{ $index }})" class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-all" title="Hapus Baris">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    @endif
                                </div>
                                <div class="md:col-span-6">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jumlah Keluar</label>
                                    @php
                                        $barangTerpilih = $barangs->firstWhere('id', $item['barang_id'] ?? '');
                                        $satuanItem = $barangTerpilih?->satuan ?? 'pcs';
                                    @endphp
                                    <div class="relative">
                                        <input wire:model="items.{{ $index }}.jumlah" type="number" min="1"
                                               class="w-full px-4 py-2 pr-12 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                        <span class="absolute inset-y-0 right-3 flex items-center text-xs font-bold text-slate-400 uppercase pointer-events-none">{{ $satuanItem }}</span>
                                    </div>
                                    @error("items.$index.jumlah") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-6">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Stok Tersedia</label>
                                    <div class="px-4 py-2 rounded-lg border border-slate-200 bg-slate-100 text-slate-700 text-sm font-bold flex items-center">
                                        @php
                                            $stokTersedia = 0;
                                            $satuanTersedia = '';
                                            if ($item['barang_id']) {
                                                $b = \App\Models\Barang::find($item['barang_id']);
                                                if ($b) {
                                                    $stokTersedia = $b->stok;
                                                    $satuanTersedia = $b->satuan;
                                                }
                                            }
                                        @endphp
                                        @if($item['barang_id'])
                                            {{ $stokTersedia }}
                                            <span class="text-xs text-slate-400 font-medium ml-1">{{ $satuanTersedia }}</span>
                                        @else
                                            <span class="text-slate-400 font-normal italic text-xs">Pilih barang dulu</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1.5">Stok sistem saat ini &mdash; otomatis, tidak diisi manual.</p>
                                </div>
                            </div>
                        </div>
                            @endforeach
                        </div>

                        @error('items') <p class="mt-4 text-red-500 text-sm font-medium">{{ $message }}</p> @enderror
                        @error('general') <p class="mt-4 text-red-500 text-sm font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-3 rounded-xl font-bold shadow-lg shadow-blue-200 transition-all active:scale-95 flex items-center disabled:opacity-70 disabled:cursor-not-allowed">

                            <span wire:loading.remove wire:target="save" class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                Simpan Barang Keluar
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

    @if($showPreview)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 max-w-2xl w-full overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-lg font-black text-slate-800 flex items-center">
                    <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002-2m-6 9l2 2 4-4"/></svg>
                    Konfirmasi & Ringkasan Barang Keluar
                </h3>
                <button type="button" wire:click="closePreview" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tanggal Keluar</p>
                            <p class="text-sm font-medium text-slate-900 mt-1">
                                {{ $tgl_keluar ? date('d F Y', strtotime($tgl_keluar)) : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Jenis Keluar</p>
                            <p class="text-sm font-medium text-slate-900 mt-1">{{ \App\Models\BarangKeluar::JENIS_KELUAR[$jenis_keluar] ?? $jenis_keluar }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Keterangan</p>
                            <p class="text-sm font-medium text-slate-700 mt-1">{{ $keterangan ?: 'Tidak ada keterangan' }}</p>
                        </div>
                    </div>
                </div>

                <div class="border border-slate-100 rounded-xl overflow-hidden shadow-sm">
                    <div class="bg-slate-50/50 px-4 py-2 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daftar Barang</span>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/20 text-slate-400 text-[10px] font-black uppercase tracking-wider border-b border-slate-100">
                                <th class="px-4 py-2">Barang</th>
                                <th class="px-4 py-2 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-slate-700 text-xs">
                            @php $totalQty = 0; @endphp
                            @foreach($items as $item)
                                @if($item['barang_id'])
                                    @php
                                        $barangObj = \App\Models\Barang::find($item['barang_id']);
                                        $totalQty += $item['jumlah'];
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-2.5">
                                            <div class="font-bold text-slate-800">{{ $barangObj?->nama_barang }}</div>
                                            <div class="font-mono text-[10px] text-slate-400">{{ $barangObj?->kode_barang }}</div>
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-bold text-slate-900">
                                            <div>{{ $item['jumlah'] }} {{ $barangObj?->satuan ?? 'pcs' }}</div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/50 text-xs font-bold border-t border-slate-100">
                            <tr>
                                <td class="px-4 py-3 text-slate-500 uppercase">Total Jumlah Item</td>
                                <td class="px-4 py-3 text-right text-slate-950 font-black">{{ $totalQty }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end space-x-3">
                <button type="button" wire:click="closePreview" class="px-4 py-2 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                    Kembali & Edit
                </button>
                <button type="button" wire:click="save" wire:loading.attr="disabled" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-bold shadow-lg shadow-blue-100 transition-all flex items-center active:scale-95 disabled:opacity-50">
                    <svg wire:loading.remove wire:target="save" class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading wire:target="save" class="animate-spin h-4 w-4 mr-1.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Konfirmasi & Simpan
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
