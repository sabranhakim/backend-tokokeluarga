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
                'barang_id' => $barang->getKey(),
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->nama_barang,
                'satuan' => $barang->satuan,
                'stok_sistem' => (int) $barang->stok,
                'stok_fisik' => (int) $barang->stok,
            ];
        }
    }

    public function syncPhysicalToSystem()
    {
        foreach ($this->items as $i => $item) {
            $this->items[$i]['stok_fisik'] = (int) $item['stok_sistem'];
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
    <div class="mb-6 flex items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <a href="{{ route('stock-opname.index') }}"
               class="mt-0.5 w-10 h-10 shrink-0 rounded-xl border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-blue-600 hover:border-blue-200 hover:shadow-sm transition-all"
               title="Kembali ke Daftar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-black text-slate-800">Buat Stock Opname</h2>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-wider border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Draft
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-0.5">Catat hasil perhitungan fisik, bandingkan dengan stok sistem, lalu simpan sebagai draft.</p>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        @error('general')
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm font-medium rounded-xl px-4 py-3">
                {{ $message }}
            </div>
        @enderror

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

                        <div x-data="{ len: {{ mb_strlen($keterangan ?? '') }} }">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-sm font-bold text-slate-700">Keterangan <span class="font-medium text-slate-400">(Opsional)</span></label>
                                <span class="text-[10px] font-bold text-slate-400 tabular-nums" x-text="len + '/30'"></span>
                            </div>
                            <textarea wire:model="keterangan" rows="3" maxlength="30"
                                      @input="len = $event.target.value.length"
                                      class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all resize-none"
                                      placeholder="Catatan pelaksanaan opname..."></textarea>
                            <p class="text-[11px] text-slate-400 mt-1">Informasi ini akan tampil di halaman detail opname.</p>
                        </div>
                    </div>

                    <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start">
                        <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <p class="text-sm font-medium text-amber-700">Data disimpan sebagai <b>draft</b>. Penyesuaian stok hanya dilakukan setelah klik <b>"Terapkan"</b> di daftar opname.</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                @php
                    $jmlBarang = count($items);
                    $perluPenyesuaian = 0;
                    $totalSelisih = 0;
                    foreach ($items as $item) {
                        $sel = (int) $item['stok_fisik'] - (int) $item['stok_sistem'];
                        $totalSelisih += $sel;
                        if ($sel !== 0) $perluPenyesuaian++;
                    }
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden"
                     x-data="{
                         search: '',
                         onlyDiff: false,
                         shown: 0,
                         init() {
                             const tbody = this.$refs.opnameRows;
                             const update = () => {
                                 this.shown = Array.from(tbody.querySelectorAll('tr.opname-row'))
                                     .filter((r) => r.style.display !== 'none').length;
                             };
                             new MutationObserver(update).observe(tbody, { attributes: true, subtree: true });
                             update();
                         }
                     }">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Daftar Stok Fisik</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Isi hasil hitungan fisik untuk setiap barang, atau biarkan sesuai stok sistem.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-600 cursor-pointer select-none bg-white px-3 py-1.5 rounded-lg border border-slate-200 hover:border-blue-200 transition-colors">
                                <input type="checkbox" x-model="onlyDiff" class="accent-blue-600 w-3.5 h-3.5">
                                Hanya yang berbeda
                            </label>
                            <div class="relative">
                                <input x-model="search"
                                       type="text"
                                       class="pl-8 pr-3 py-1.5 text-sm border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                                       placeholder="Cari kode / nama barang...">
                                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <span class="text-xs font-bold text-slate-500 px-2 py-1 bg-slate-100 rounded-lg tabular-nums" x-text="shown + ' / ' + {{ $jmlBarang }}"></span>
                        </div>
                    </div>

                    <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Total Barang</p>
                                <p class="text-lg font-black text-slate-800 tabular-nums">{{ $jmlBarang }}</p>
                            </div>
                        </div>
                        <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Perlu Penyesuaian</p>
                                <p class="text-lg font-black {{ $perluPenyesuaian > 0 ? 'text-amber-600' : 'text-slate-800' }} tabular-nums">{{ $perluPenyesuaian }}</p>
                            </div>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg {{ $totalSelisih < 0 ? 'bg-red-50 text-red-600' : ($totalSelisih > 0 ? 'bg-green-50 text-green-600' : 'bg-slate-100 text-slate-500') }} flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Total Selisih</p>
                                <p class="text-lg font-black {{ $totalSelisih < 0 ? 'text-red-600' : ($totalSelisih > 0 ? 'text-green-600' : 'text-slate-800') }} tabular-nums">
                                    {{ $totalSelisih > 0 ? '+' : '' }}{{ $totalSelisih }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 flex items-center justify-between gap-3">
                        <button type="button"
                                wire:click="syncPhysicalToSystem"
                                wire:loading.attr="disabled"
                                wire:target="syncPhysicalToSystem"
                                class="text-xs font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 px-3 py-2 rounded-lg transition-colors flex items-center disabled:opacity-50">
                            <svg wire:loading.remove wire:target="syncPhysicalToSystem" class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <svg wire:loading wire:target="syncPhysicalToSystem" class="animate-spin w-4 h-4 mr-1.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Set Fisik = Stok Sistem
                        </button>
                        <p class="text-xs text-slate-400 hidden md:block">Klik untuk menandai semua barang sesuai sistem (selisih 0).</p>
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
                                <tbody class="divide-y divide-slate-50" x-ref="opnameRows">
                                    @foreach($items as $index => $item)
                                    @php $selisih = (int) $item['stok_fisik'] - (int) $item['stok_sistem']; @endphp
                                    <tr wire:key="opname-{{ $index }}"
                                        class="opname-row transition-colors {{ $selisih !== 0 ? ($selisih > 0 ? 'bg-green-50/40' : 'bg-red-50/40') : '' }}"
                                        x-show="(!onlyDiff || {{ $selisih }} != 0) && (search === '' || '{{ $item['kode_barang'] }} {{ $item['nama_barang'] }}'.toLowerCase().includes(search.toLowerCase()))">
                                        <td class="px-4 py-2.5">
                                            <span class="font-mono text-[11px] font-bold text-slate-500 bg-slate-100 rounded-md px-2 py-1">{{ $item['kode_barang'] }}</span>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span class="text-sm font-medium text-slate-800">{{ $item['nama_barang'] }}</span>
                                            <span class="inline-block ml-1.5 text-[10px] font-bold text-slate-400 bg-slate-50 border border-slate-100 rounded px-1.5 py-0.5 uppercase">{{ $item['satuan'] }}</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            <span class="inline-flex items-center gap-1 text-sm font-bold text-slate-500 tabular-nums">
                                                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $item['stok_sistem'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            <div class="inline-flex items-center rounded-lg border border-slate-200 bg-white overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 transition-all">
                                                <button type="button"
                                                        @click="
                                                            const input = $event.currentTarget.parentElement.querySelector('input');
                                                            const next = Math.max(0, (parseInt(input.value) || 0) - 1);
                                                            input.value = next;
                                                            input.dispatchEvent(new Event('input', { bubbles: true }));
                                                            input.dispatchEvent(new Event('change', { bubbles: true }));
                                                        "
                                                        class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                        tabindex="-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                                </button>
                                                <input type="number" min="0" inputmode="numeric"
                                                       wire:model.debounce.300ms="items.{{ $index }}.stok_fisik"
                                                       class="w-16 text-right px-1 py-1.5 outline-none text-sm font-bold text-slate-800 tabular-nums [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                                <button type="button"
                                                        @click="
                                                            const input = $event.currentTarget.parentElement.querySelector('input');
                                                            const next = Math.max(0, (parseInt(input.value) || 0) + 1);
                                                            input.value = next;
                                                            input.dispatchEvent(new Event('input', { bubbles: true }));
                                                            input.dispatchEvent(new Event('change', { bubbles: true }));
                                                        "
                                                        class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                        tabindex="-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            @if($selisih == 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Sesuai
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $selisih > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $selisih > 0 ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                                    {{ $selisih > 0 ? '+' : '' }}{{ $selisih }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach

                                    <tr x-cloak
                                        x-show="(search !== '' || onlyDiff) && shown === 0"
                                        class="text-center py-10">
                                        <td colspan="5" class="px-4 py-12">
                                            <div class="flex flex-col items-center justify-center text-slate-400">
                                                <svg class="w-10 h-10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                <p class="text-sm font-bold text-slate-500">Tidak ada barang yang ditampilkan</p>
                                                <p class="text-xs text-slate-400 mt-1" x-text="search !== '' ? 'Ubah kata kunci pencarian Anda.' : 'Semua barang sudah sesuai dengan stok sistem.'"></p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="flex items-center gap-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                                Bertambah
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                Berkurang
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                                Sesuai
                            </span>
                        </div>

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
