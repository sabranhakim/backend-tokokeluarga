<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <div class="space-y-8 pb-8">
        <livewire:dashboard-overview />
        
        <div class="px-6">
            <livewire:dashboard-chart />
        </div>
    </div>
</x-app-layout>
