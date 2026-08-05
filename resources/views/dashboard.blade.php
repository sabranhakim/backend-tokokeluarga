<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <div class="space-y-8 pb-8">
        <livewire:dashboard-overview />
        
        <div class="px-6 grid grid-cols-1 xl:grid-cols-2 gap-6">
            <livewire:dashboard-chart />
            <livewire:dashboard-chart-barang-keluar />
        </div>
    </div>
</x-app-layout>
