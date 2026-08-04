<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ReturPembelian;
use App\Services\ReturPembelianService;

class ReturPembelianController extends Controller
{
    protected $returPembelianService;

    public function __construct(ReturPembelianService $returPembelianService)
    {
        $this->returPembelianService = $returPembelianService;
    }

    public function index()
    {
        return view('retur-pembelian.index');
    }

    public function create()
    {
        return view('retur-pembelian.create');
    }

    public function show(ReturPembelian $returPembelian)
    {
        $returPembelian->load(['supplier', 'user', 'detailReturPembelians.barang', 'detailReturPembelians.barangStok']);

        return view('retur-pembelian.show', compact('returPembelian'));
    }

    public function destroy(ReturPembelian $returPembelian)
    {
        $returPembelian->delete();

        return redirect()->route('retur-pembelian.index')
            ->with('success', 'Data retur pembelian berhasil dihapus.');
    }
}
