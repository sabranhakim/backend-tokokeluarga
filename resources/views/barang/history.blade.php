<x-app-layout>
    <x-slot name="header">
        Riwayat Stok: {{ $barang->nama_barang }}
    </x-slot>

    <livewire:barang.barang-history :barang-id="$barang->id" />
</x-app-layout>
