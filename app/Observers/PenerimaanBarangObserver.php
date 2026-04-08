<?php

namespace App\Observers;

use App\Models\PenerimaanBarang;
use App\Models\User;
use App\Notifications\NewPenerimaanNotification;
use Illuminate\Support\Facades\Notification;

class PenerimaanBarangObserver
{
    /**
     * Handle the PenerimaanBarang "created" event.
     */
    public function created(PenerimaanBarang $penerimaanBarang): void
    {
        // Load relationships needed for notification message
        $penerimaanBarang->load(['supplier', 'user']);
        
        $users = User::all();
        Notification::send($users, new NewPenerimaanNotification($penerimaanBarang));
    }
}
