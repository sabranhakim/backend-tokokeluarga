<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }
};
?>

<div x-data="{ open: false }">
    <button @click="open = true" class="flex items-center px-4 py-2 text-sm font-bold text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all duration-200 group">
        <div class="p-2 rounded-lg bg-slate-50 group-hover:bg-rose-100 group-hover:text-rose-600 transition-colors mr-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
        </div>
        <span>Keluar</span>
    </button>

    <!-- Logout Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="open" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4"
             x-cloak>
            <!-- Overlay -->
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="open = false"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <!-- Modal Content -->
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-100">
                
                <div class="p-8 text-center">
                    <div class="w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-6 text-rose-500">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    
                    <h3 class="text-xl font-black text-slate-800 mb-2">Konfirmasi Keluar</h3>
                    <p class="text-slate-500 font-medium">Apakah Anda yakin ingin mengakhiri sesi ini dan keluar dari aplikasi?</p>
                </div>

                <div class="p-6 bg-slate-50/50 flex gap-3">
                    <button @click="open = false" 
                            class="flex-1 px-6 py-3 rounded-2xl font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 transition-all">
                        Batal
                    </button>
                    <button wire:click="logout" 
                            class="flex-1 px-6 py-3 rounded-2xl font-bold text-white bg-rose-500 hover:bg-rose-600 shadow-lg shadow-rose-100 transition-all active:scale-95">
                        Ya, Keluar
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
