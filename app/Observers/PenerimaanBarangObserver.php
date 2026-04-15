<?php

namespace App\Observers;

use App\Models\PenerimaanBarang;

class PenerimaanBarangObserver
{
    /**
     * Handle the PenerimaanBarang "created" event.
     */
    public function created(PenerimaanBarang $penerimaanBarang): void
    {
        // Notifikasi dipindahkan ke PenerimaanBarangService
        // untuk memastikan detail barang sudah tersimpan sebelum dikirim.
    }
}
