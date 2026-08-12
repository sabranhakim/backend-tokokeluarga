<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\DetailReturPenjualan;
use App\Models\ReturPenjualan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReturPenjualanService
{
    protected $stockHelper;

    public function __construct(StockHelperService $stockHelper)
    {
        $this->stockHelper = $stockHelper;
    }

    public function store(array $data): ReturPenjualan
    {
        return DB::transaction(function () use ($data) {
            $noRetur = $this->generateNoRetur();

            $retur = ReturPenjualan::create([
                'no_retur' => $noRetur,
                'user_id' => auth()->id(),
                'tgl_retur' => $data['tgl_retur'],
                'nama_pelanggan' => $data['nama_pelanggan'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            $reason = "Retur Penjualan #{$retur->no_retur}";

            foreach ($data['items'] as $item) {
                $barang = Barang::findOrFail($item['barang_id']);

                $this->stockHelper->increase(
                    $barang,
                    (int) $item['jumlah'],
                    $retur->no_retur,
                    $data['tgl_retur'],
                    $reason,
                    auth()->id(),
                    $retur,
                    'in'
                );

                DetailReturPenjualan::create([
                    'retur_penjualan_id' => $retur->getKey(),
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                ]);

                activity()
                    ->performedOn($barang)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'old_stok' => $barang->stok - $item['jumlah'],
                        'new_stok' => $barang->stok,
                        'jumlah_retur' => $item['jumlah'],
                        'no_retur' => $retur->no_retur,
                        'tipe' => 'retur_penjualan',
                    ])
                    ->log("Stok barang '{$barang->nama_barang}' bertambah {$item['jumlah']} pcs melalui retur penjualan {$retur->no_retur}");
            }

            return $retur->load(['user', 'detailReturPenjualans.barang']);
        });
    }

    public function getAll(): Collection
    {
        return ReturPenjualan::with(['user', 'detailReturPenjualans.barang'])->latest()->get();
    }

    public function getById(string $id): ReturPenjualan
    {
        return ReturPenjualan::with(['user', 'detailReturPenjualans.barang'])->findOrFail($id);
    }

    public function generateNoRetur(): string
    {
        do {
            $noRetur = 'RPJ-'.date('Ymd').strtoupper(bin2hex(random_bytes(3)));
        } while (ReturPenjualan::where('no_retur', $noRetur)->exists());

        return $noRetur;
    }
}
