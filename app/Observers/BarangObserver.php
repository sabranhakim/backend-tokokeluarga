<?php

namespace App\Observers;

use App\Models\Barang;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;

class BarangObserver
{
    public function updated(Barang $barang): void
    {
        if ($barang->stok <= $barang->stok_minimal) {
            $users = User::all();
            Notification::send($users, new LowStockNotification($barang));
        }
    }

    public function created(Barang $barang): void
    {
        if ($barang->stok <= $barang->stok_minimal) {
            $users = User::all();
            Notification::send($users, new LowStockNotification($barang));
        }
    }
}
