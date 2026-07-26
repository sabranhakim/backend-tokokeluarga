<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangStok;
use App\Models\DetailBarangKeluar;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BarangKeluarService
{
    public function store(array $data): BarangKeluar
    {
        return DB::transaction(function () use ($data) {
            $noKeluar = $this->generateNoKeluar();

            $barangKeluar = BarangKeluar::create([
                'no_keluar' => $noKeluar,
                'user_id' => auth()->id(),
                'tgl_keluar' => $data['tgl_keluar'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $barang = Barang::where('id', $item['barang_id'])->lockForUpdate()->firstOrFail();

                $sisaKurang = $item['jumlah'];

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

                    $ambilDariBatch = min($batch->stok, $sisaKurang);
                    $stokSebelum = $batch->stok;
                    $batch->decrement('stok', $ambilDariBatch);
                    $sisaKurang -= $ambilDariBatch;
                    $totalDiambil += $ambilDariBatch;

                    DetailBarangKeluar::create([
                        'barang_keluar_id' => $barangKeluar->id,
                        'barang_id' => $barang->id,
                        'barang_stok_id' => $batch->id,
                        'jumlah' => $ambilDariBatch,
                    ]);

                    StockMovement::create([
                        'barang_id' => $barang->id,
                        'barang_stok_id' => $batch->id,
                        'user_id' => auth()->id(),
                        'type' => 'out',
                        'quantity' => $ambilDariBatch,
                        'before_quantity' => $stokSebelum,
                        'after_quantity' => $stokSebelum - $ambilDariBatch,
                        'reason' => "Barang Keluar #{$barangKeluar->no_keluar}" . ($data['keterangan'] ? " ({$data['keterangan']})" : ""),
                        'reference_id' => $barangKeluar->id,
                        'reference_type' => BarangKeluar::class,
                    ]);
                }

                $barang->decrement('stok', $totalDiambil);

                $konversi = '';
                if ($barang->isi && $barang->isi > 1) {
                    $kemasan = floor($totalDiambil / $barang->isi);
                    $sisaPcs = $totalDiambil % $barang->isi;
                    if ($kemasan > 0) {
                        $konversi = " ({$kemasan} {$barang->satuan}";
                        $konversi .= $sisaPcs > 0 ? " + {$sisaPcs} pcs)" : ")";
                    }
                }

                activity()
                    ->performedOn($barang)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'old_stok' => $barang->stok + $totalDiambil,
                        'new_stok' => $barang->stok,
                        'jumlah_keluar' => $totalDiambil,
                        'no_keluar' => $barangKeluar->no_keluar,
                        'tipe' => 'keluar',
                    ])
                    ->log("Stok barang '{$barang->nama_barang}' berkurang {$totalDiambil} pcs{$konversi} melalui {$barangKeluar->no_keluar}");
            }

            return $barangKeluar->load(['user', 'detailBarangKeluars.barang']);
        });
    }

    public function getAll(): Collection
    {
        return BarangKeluar::with(['user', 'detailBarangKeluars.barang'])->latest()->get();
    }

    public function getById(string $id): BarangKeluar
    {
        return BarangKeluar::with(['user', 'detailBarangKeluars.barang', 'detailBarangKeluars.barangStok'])->findOrFail($id);
    }

    public function generateNoKeluar(): string
    {
        do {
            $noKeluar = 'KLR-' . date('Ymd') . strtoupper(bin2hex(random_bytes(3)));
        } while (BarangKeluar::where('no_keluar', $noKeluar)->exists());

        return $noKeluar;
    }
}
