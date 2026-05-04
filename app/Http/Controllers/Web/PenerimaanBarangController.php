<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanBarang;
use App\Services\PenerimaanBarangService;

class PenerimaanBarangController extends Controller
{
    protected $penerimaanService;

    public function __construct(PenerimaanBarangService $penerimaanService)
    {
        $this->penerimaanService = $penerimaanService;
    }

    public function index()
    {
        return view('penerimaan.index');
    }

    public function create()
    {
        return view('penerimaan.create');
    }

    public function show(PenerimaanBarang $penerimaanBarang)
    {
        return view('penerimaan.show', compact('penerimaanBarang'));
    }

    public function trash()
    {
        return view('penerimaan.trash');
    }

    public function verify(PenerimaanBarang $penerimaanBarang)
    {
        try {
            $this->penerimaanService->verify($penerimaanBarang->id);

            return redirect()->back()
                ->with('success', 'Penerimaan barang berhasil diverifikasi dan stok telah diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memverifikasi: ' . $e->getMessage());
        }
    }

    public function destroy(PenerimaanBarang $penerimaanBarang)
    {
        $penerimaanBarang->delete();

        return redirect()->route('penerimaan.index')
            ->with('success', 'Data penerimaan berhasil dihapus.');
    }
}
