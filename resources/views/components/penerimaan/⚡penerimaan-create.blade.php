<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Supplier;
use App\Models\Barang;
use App\Services\PenerimaanBarangService;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithFileUploads;

    // Header fields
    public $no_terima;
    public $supplier_id;
    public $tgl_terima;
    public $foto_bon;

    // Items list
    public $items = [];

    protected $rules = [
        'no_terima' => 'required|unique:penerimaan_barangs,no_terima',
        'supplier_id' => 'required|exists:suppliers,id',
        'tgl_terima' => 'required|date',
        'foto_bon' => 'nullable|image|max:5120',
        'items' => 'required|array|min:1',
        'items.*.barang_id' => 'required|exists:barangs,id',
        'items.*.jumlah' => 'required|numeric|min:1',
    ];

    public function mount()
    {
        $this->tgl_terima = date('Y-m-d');
        $this->no_terima = 'TRM-' . date('Ymd') . strtoupper(bin2hex(random_bytes(3)));
        $this->addItem(); // Start with one empty item
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

    public function save(PenerimaanBarangService $service)
    {
        $validated = $this->validate();

        try {
            $service->store($validated, $this->foto_bon);
            
            session()->flash('success', 'Penerimaan barang berhasil disimpan.');
            return redirect()->route('penerimaan.index');
        } catch (\Exception $e) {
            $this->addError('general', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function with()
    {
        return [
            'suppliers' => Supplier::all(),
            'barangs' => Barang::all(),
        ];
    }
};
?>

<div class="p-6">
    <div class="mb-6">
        <a href="{{ route('penerimaan.index') }}" class="text-blue-600 hover:text-blue-700 font-medium flex items-center transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar
        </a>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Side: Header Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-50 pb-2">Informasi Penerimaan</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">No. Terima</label>
                            <input wire:model="no_terima" type="text" placeholder="TRM-{{ date('YmdHis') }}" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('no_terima') border-red-500 @enderror">
                            @error('no_terima') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Tanggal Terima</label>
                            <input wire:model="tgl_terima" type="date" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('tgl_terima') border-red-500 @enderror">
                            @error('tgl_terima') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Supplier</label>
                            <select wire:model="supplier_id" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all @error('supplier_id') border-red-500 @enderror">
                                <option value="">Pilih Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Foto Bon (Opsional)</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-xl hover:border-blue-400 transition-colors cursor-pointer relative">
                                <input type="file" wire:model="foto_bon" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-10 w-10 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-slate-600">
                                        <span class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">Upload file</span>
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG up to 5MB</p>
                                </div>
                            </div>
                            @if ($foto_bon)
                                <div class="mt-2 relative inline-block">
                                    <img src="{{ $foto_bon->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-lg">
                                    <button type="button" wire:click="$set('foto_bon', null)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-sm">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endif
                            @error('foto_bon') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Items List -->
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
                            @foreach($items as $index => $item)
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-slate-50/50 p-4 rounded-xl border border-slate-100 relative group transition-all hover:bg-slate-50">
                                <div class="md:col-span-7">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Barang</label>
                                    <select wire:model="items.{{ $index }}.barang_id" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                        <option value="">Pilih Barang</option>
                                        @foreach($barangs as $barang)
                                            <option value="{{ $barang->id }}">{{ $barang->kode_barang }} - {{ $barang->nama_barang }} ({{ $barang->satuan }})</option>
                                        @endforeach
                                    </select>
                                    @error("items.$index.barang_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-4">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jumlah</label>
                                    <input wire:model="items.{{ $index }}.jumlah" type="number" class="w-full px-4 py-2 rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                    @error("items.$index.jumlah") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-1 flex justify-center">
                                    @if(count($items) > 1)
                                    <button type="button" wire:click="removeItem({{ $index }})" class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-all" title="Hapus Baris">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @error('items') <p class="mt-4 text-red-500 text-sm font-medium">{{ $message }}</p> @enderror
                        @error('general') <p class="mt-4 text-red-500 text-sm font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-3 rounded-xl font-bold shadow-lg shadow-blue-200 transition-all active:scale-95 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Simpan Penerimaan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
