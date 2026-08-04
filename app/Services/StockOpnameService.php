<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\DetailStockOpname;
use App\Models\StockOpname;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StockOpnameService
{
    protected $stockHelper;

    public function __construct(StockHelperService $stockHelper)
    {
        $this->stockHelper = $stockHelper;
    }

    public function store(array $data): StockOpname
    {
        return DB::transaction(function () use ($data) {
            $noOpname = $this->generateNoOpname();

            $opname = StockOpname::create([
                'no_opname' => $noOpname,
                'user_id' => auth()->id(),
                'tgl_opname' => $data['tgl_opname'],
                'keterangan' => $data['keterangan'] ?? null,
                'status' => 'draft',
            ]);

            foreach ($data['items'] as $item) {
                $barang = Barang::findOrFail($item['barang_id']);
                $stokSistem = (int) $barang->stok;
                $stokFisik = (int) $item['stok_fisik'];

                DetailStockOpname::create([
                    'stock_opname_id' => $opname->id,
                    'barang_id' => $barang->id,
                    'stok_sistem' => $stokSistem,
                    'stok_fisik' => $stokFisik,
                    'selisih' => $stokFisik - $stokSistem,
                ]);
            }

            return $opname->load(['user', 'detailStockOpnames.barang']);
        });
    }

    public function finalize(string $id): StockOpname
    {
        return DB::transaction(function () use ($id) {
            $opname = StockOpname::with(['detailStockOpnames.barang'])->findOrFail($id);

            if ($opname->status === 'selesai') {
                throw new \Exception('Stock opname sudah diterapkan sebelumnya.');
            }

            $reason = "Stock Opname #{$opname->no_opname}";
            $totalSelisih = 0;

            foreach ($opname->detailStockOpnames as $detail) {
                if ($detail->selisih == 0) {
                    continue;
                }

                $totalSelisih += $detail->selisih;

                $this->stockHelper->adjust(
                    $detail->barang,
                    $detail->selisih,
                    $opname->no_opname,
                    $opname->tgl_opname->format('Y-m-d'),
                    $reason,
                    auth()->id(),
                    $opname
                );

                activity()
                    ->performedOn($detail->barang)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'stok_sistem' => $detail->stok_sistem,
                        'stok_fisik' => $detail->stok_fisik,
                        'selisih' => $detail->selisih,
                        'no_opname' => $opname->no_opname,
                        'tipe' => 'stock_opname',
                    ])
                    ->log("Stock opname '{$detail->barang->nama_barang}': sistem {$detail->stok_sistem}, fisik {$detail->stok_fisik}, selisih {$detail->selisih} (via {$opname->no_opname})");
            }

            $opname->update([
                'status' => 'selesai',
                'total_selisih' => $totalSelisih,
            ]);

            return $opname->load(['user', 'detailStockOpnames.barang']);
        });
    }

    public function getAll(): Collection
    {
        return StockOpname::with(['user'])->latest()->get();
    }

    public function getById(string $id): StockOpname
    {
        return StockOpname::with(['user', 'detailStockOpnames.barang'])->findOrFail($id);
    }

    public function generateNoOpname(): string
    {
        do {
            $noOpname = 'OPN-'.date('Ymd').strtoupper(bin2hex(random_bytes(3)));
        } while (StockOpname::where('no_opname', $noOpname)->exists());

        return $noOpname;
    }
}
