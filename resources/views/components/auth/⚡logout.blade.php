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

<button wire:click="logout" class="text-sm font-medium text-slate-600 hover:text-red-600">
    Keluar
</button>
