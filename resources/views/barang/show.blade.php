<x-app-layout>
    <x-slot name="header">
        Detail Barang: {{ $barang->nama_barang }}
    </x-slot>

    <livewire:barang.barang-detail :barang-id="$barang->getKey()" />
</x-app-layout>
