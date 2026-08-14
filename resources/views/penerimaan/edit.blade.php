<x-app-layout>
    <x-slot name="header">
        Edit Penerimaan Barang #{{ $penerimaanBarang->no_terima }}
    </x-slot>

    <livewire:penerimaan.penerimaan-create :penerimaanId="$penerimaanBarang->getKey()" />
</x-app-layout>