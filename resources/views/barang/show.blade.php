<x-app-layout>
    <x-slot name="header">
        Detail Barang: {{ $barang->nama_barang }}
    </x-slot>

    <livewire:barang.barang-detail :barang-id="$barang->id" />
</x-app-layout>
