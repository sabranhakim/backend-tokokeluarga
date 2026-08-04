<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\BarangStok;
use App\Models\StockMovement;

class StockHelperService
{
    /**
     * Reduce barang stock using FEFO across batches (oldest expiry first).
     *
     * @param  callable(BarangStok $batch, int $amount, int $stokBefore): void  $onBatchTaken
     * @return int total amount taken
     */
    public function reduce(Barang $barang, int $jumlah, string $reason, int $userId, object $reference, string $type = 'out', ?callable $onBatchTaken = null): int
    {
        $barang = Barang::where('id', $barang->id)->lockForUpdate()->firstOrFail();
        $sisaKurang = $jumlah;

        $batches = BarangStok::where('barang_id', $barang->id)
            ->where('stok', '>', 0)
            ->orderBy('tgl_kadaluarsa', 'asc')
            ->orderBy('tgl_masuk', 'asc')
            ->get();

        if ($batches->isEmpty()) {
            throw new \Exception("Stok barang '{$barang->nama_barang}' tidak tersedia.");
        }

        $totalTersedia = $batches->sum('stok');
        if ($sisaKurang > $totalTersedia) {
            throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi. Tersedia: {$totalTersedia}, diminta: {$sisaKurang}");
        }

        $totalDiambil = 0;

        foreach ($batches as $batch) {
            if ($sisaKurang <= 0) {
                break;
            }

            $ambil = min($batch->stok, $sisaKurang);
            $stokSebelum = $batch->stok;
            $batch->decrement('stok', $ambil);
            $sisaKurang -= $ambil;
            $totalDiambil += $ambil;

            if ($onBatchTaken) {
                $onBatchTaken($batch, $ambil, $stokSebelum);
            }

            StockMovement::create([
                'barang_id' => $barang->id,
                'barang_stok_id' => $batch->id,
                'user_id' => $userId,
                'type' => $type,
                'quantity' => $ambil,
                'before_quantity' => $stokSebelum,
                'after_quantity' => $stokSebelum - $ambil,
                'reason' => $reason,
                'reference_id' => $reference->id,
                'reference_type' => get_class($reference),
            ]);
        }

        $barang->decrement('stok', $totalDiambil);

        return $totalDiambil;
    }

    /**
     * Increase barang stock by creating a new batch entry.
     */
    public function increase(Barang $barang, int $jumlah, ?string $batchNumber, string $tglMasuk, string $reason, int $userId, object $reference, string $type = 'in'): BarangStok
    {
        $stokSebelum = $barang->stok;

        $barangStok = BarangStok::create([
            'barang_id' => $barang->id,
            'batch_number' => $batchNumber,
            'stok' => $jumlah,
            'tgl_masuk' => $tglMasuk,
            'harga_beli' => $barang->harga_beli,
        ]);

        $barang->increment('stok', $jumlah);

        StockMovement::create([
            'barang_id' => $barang->id,
            'barang_stok_id' => $barangStok->id,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $jumlah,
            'before_quantity' => $stokSebelum,
            'after_quantity' => $barang->stok,
            'reason' => $reason,
            'reference_id' => $reference->id,
            'reference_type' => get_class($reference),
        ]);

        return $barangStok;
    }

    /**
     * Adjust stock based on a selisih value (positive = increase, negative = reduce).
     */
    public function adjust(Barang $barang, int $selisih, ?string $batchNumber, string $tgl, string $reason, int $userId, object $reference, ?callable $onBatchTaken = null): void
    {
        if ($selisih > 0) {
            $this->increase($barang, $selisih, $batchNumber, $tgl, $reason, $userId, $reference, 'adjustment');
        } elseif ($selisih < 0) {
            $this->reduce($barang, abs($selisih), $reason, $userId, $reference, 'adjustment', $onBatchTaken);
        }
    }
}
