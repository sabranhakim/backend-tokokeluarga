<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\DetailPenerimaan;
use App\Models\PenerimaanBarang;
use Illuminate\Support\Facades\DB;

class PenerimaanBarangService
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Store a new penerimaan barang along with its details.
     *
     * @param array $data
     * @param mixed $file
     * @return \App\Models\PenerimaanBarang
     */
    public function store(array $data, $file = null)
    {
        return DB::transaction(function () use ($data, $file) {
            $fotoBonUrl = null;
            if ($file) {
                $fotoBonUrl = $this->cloudinaryService->upload($file);
            }

            // 1. Create PenerimaanBarang Header
            $penerimaan = PenerimaanBarang::create([
                'no_terima' => $data['no_terima'],
                'supplier_id' => $data['supplier_id'],
                'user_id' => auth()->id(),
                'tgl_terima' => $data['tgl_terima'],
                'foto_bon' => $fotoBonUrl,
                'status_verifikasi' => 'pending', // Default is pending
            ]);

            // 2. Create Details and Update Stock
            foreach ($data['items'] as $item) {
                DetailPenerimaan::create([
                    'penerimaan_barang_id' => $penerimaan->id,
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                ]);

                // Update stock in Barang table
                $barang = Barang::findOrFail($item['barang_id']);
                $barang->increment('stok', $item['jumlah']);
            }

            return $penerimaan->load('detailPenerimaans.barang', 'supplier', 'user');
        });
    }

    /**
     * Get all penerimaan barang with relationships.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return PenerimaanBarang::with(['supplier', 'user', 'detailPenerimaans.barang'])->latest()->get();
    }

    /**
     * Get a single penerimaan barang with relationships.
     *
     * @param int $id
     * @return \App\Models\PenerimaanBarang
     */
    public function getById($id)
    {
        return PenerimaanBarang::with(['supplier', 'user', 'detailPenerimaans.barang'])->findOrFail($id);
    }
}
