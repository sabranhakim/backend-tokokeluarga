<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\DetailBarangKeluar;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BarangKeluarService
{
    protected $stockHelper;

    public function __construct(StockHelperService $stockHelper)
    {
        $this->stockHelper = $stockHelper;
    }

    public function store(array $data): BarangKeluar
    {
        return DB::transaction(function () use ($data) {
            $noKeluar = $this->generateNoKeluar();

            $barangKeluar = BarangKeluar::create([
                'no_keluar' => $noKeluar,
                'user_id' => auth()->id(),
                'tgl_keluar' => $data['tgl_keluar'],
                'jenis_keluar' => $data['jenis_keluar'] ?? 'penjualan',
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            $reason = "Barang Keluar #{$barangKeluar->no_keluar} ({$barangKeluar->jenis_keluar_label})".($data['keterangan'] ? " - {$data['keterangan']}" : '');

            foreach ($data['items'] as $item) {
                $barang = Barang::findOrFail($item['barang_id']);

                $totalDiambil = $this->stockHelper->reduce(
                    $barang,
                    (int) $item['jumlah'],
                    $reason,
                    auth()->id(),
                    $barangKeluar,
                    'out',
                    function ($batch, $ambil) use ($barangKeluar, $item) {
                        DetailBarangKeluar::create([
                            'barang_keluar_id' => $barangKeluar->id,
                            'barang_id' => $item['barang_id'],
                            'barang_stok_id' => $batch->id,
                            'jumlah' => $ambil,
                        ]);
                    }
                );

                activity()
                    ->performedOn($barang)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'old_stok' => $barang->stok + $totalDiambil,
                        'new_stok' => $barang->stok,
                        'jumlah_keluar' => $totalDiambil,
                        'no_keluar' => $barangKeluar->no_keluar,
                        'jenis_keluar' => $barangKeluar->jenis_keluar,
                        'tipe' => 'keluar',
                    ])
                    ->log("Stok barang '{$barang->nama_barang}' berkurang {$totalDiambil} {$barang->satuan} melalui {$barangKeluar->no_keluar}");
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
            $noKeluar = 'KLR-'.date('Ymd').strtoupper(bin2hex(random_bytes(3)));
        } while (BarangKeluar::where('no_keluar', $noKeluar)->exists());

        return $noKeluar;
    }
}
