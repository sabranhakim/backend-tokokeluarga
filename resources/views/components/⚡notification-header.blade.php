<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public function getNotifications()
    {
        return Auth::user()->unreadNotifications;
    }

    public function markAsRead($id)
    {
        Auth::user()->unreadNotifications->where('id', $id)->markAsRead();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }
};
?>

<div x-data="{ open: false }" class="relative">
    <!-- Notification Bell -->
    <button @click="open = !open" class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($this->getNotifications()->count() > 0)
            <span class="absolute top-1 right-1 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] text-white items-center justify-center font-bold">
                    {{ $this->getNotifications()->count() }}
                </span>
            </span>
        @endif
    </button>

    <!-- Dropdown -->
    <div x-show="open" 
         @click.outside="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden"
         style="display: none;">
        
        <div class="px-4 py-3 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-800">Notifikasi</h3>
            @if($this->getNotifications()->count() > 0)
                <button wire:click="markAllAsRead" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Tandai semua dibaca</button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($this->getNotifications() as $notification)
                <div class="px-4 py-3 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0 relative group">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-1">
                            @if(($notification->data['type'] ?? '') == 'low_stock')
                                <div class="p-2 bg-amber-50 rounded-lg">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                            @else
                                <div class="p-2 bg-blue-50 rounded-lg">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm text-slate-800 leading-relaxed">
                                {{ $notification->data['message'] ?? 'Notifikasi baru' }}
                            </p>
                            <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold tracking-wider">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <button wire:click="markAsRead('{{ $notification->id }}')" class="ml-2 text-slate-300 hover:text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-2.586 2.586a2 2 0 01-2.828 0L12 14l-2.586 2.586a2 2 0 01-2.828 0L4 13"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500 italic">Tidak ada notifikasi baru.</p>
                </div>
            @endforelse
        </div>
        
        @if($this->getNotifications()->count() > 0)
            <div class="px-4 py-2 border-t border-slate-50 bg-slate-50/30 text-center">
                <a href="#" class="text-xs font-bold text-slate-500 hover:text-slate-700">Lihat Semua</a>
            </div>
        @endif
    </div>
</div>
