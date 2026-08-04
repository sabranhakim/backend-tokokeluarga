<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\DetailReturPembelian;
use App\Models\ReturPembelian;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReturPembelianService
{
    protected $stockHelper;

    public function __construct(StockHelperService $stockHelper)
    {
        $this->stockHelper = $stockHelper;
    }

    public function store(array $data): ReturPembelian
    {
        return DB::transaction(function () use ($data) {
            $noRetur = $this->generateNoRetur();

            $retur = ReturPembelian::create([
                'no_retur' => $noRetur,
                'supplier_id' => $data['supplier_id'] ?? null,
                'user_id' => auth()->id(),
                'tgl_retur' => $data['tgl_retur'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            $reason = "Retur Pembelian #{$retur->no_retur}";

            foreach ($data['items'] as $item) {
                $barang = Barang::findOrFail($item['barang_id']);

                $totalDiambil = $this->stockHelper->reduce(
                    $barang,
                    (int) $item['jumlah'],
                    $reason,
                    auth()->id(),
                    $retur,
                    'out',
                    function ($batch, $ambil) use ($retur, $item) {
                        DetailReturPembelian::create([
                            'retur_pembelian_id' => $retur->id,
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
                        'jumlah_retur' => $totalDiambil,
                        'no_retur' => $retur->no_retur,
                        'tipe' => 'retur_pembelian',
                    ])
                    ->log("Stok barang '{$barang->nama_barang}' berkurang {$totalDiambil} pcs melalui retur pembelian {$retur->no_retur}");
            }

            return $retur->load(['supplier', 'user', 'detailReturPembelians.barang']);
        });
    }

    public function getAll(): Collection
    {
        return ReturPembelian::with(['supplier', 'user', 'detailReturPembelians.barang'])->latest()->get();
    }

    public function getById(string $id): ReturPembelian
    {
        return ReturPembelian::with(['supplier', 'user', 'detailReturPembelians.barang', 'detailReturPembelians.barangStok'])->findOrFail($id);
    }

    public function generateNoRetur(): string
    {
        do {
            $noRetur = 'RPB-'.date('Ymd').strtoupper(bin2hex(random_bytes(3)));
        } while (ReturPembelian::where('no_retur', $noRetur)->exists());

        return $noRetur;
    }
}
