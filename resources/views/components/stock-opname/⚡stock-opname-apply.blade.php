<?php

use Livewire\Component;
use App\Models\StockOpname;
use App\Services\StockOpnameService;

new class extends Component {
    public $stockOpnameId;

    public function mount(string $stockOpnameId)
    {
        $this->stockOpnameId = $stockOpnameId;
    }

    public function apply(StockOpnameService $service)
    {
        try {
            $service->finalize($this->stockOpnameId);
            session()->flash('success', 'Stock opname berhasil diterapkan dan stok telah disesuaikan.');
            return $this->redirect(route('stock-opname.show', $this->stockOpnameId));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }
};
?>

<div>
    <button wire:click="apply" wire:confirm="Terapkan penyesuaian stok untuk stock opname ini?"
            wire:loading.attr="disabled"
            class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-200 transition-all active:scale-95 flex items-center disabled:opacity-70">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Terapkan Opname
    </button>
</div>
