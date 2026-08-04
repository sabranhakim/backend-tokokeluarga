<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ReturPenjualan;
use App\Services\ReturPenjualanService;

class ReturPenjualanController extends Controller
{
    protected $returPenjualanService;

    public function __construct(ReturPenjualanService $returPenjualanService)
    {
        $this->returPenjualanService = $returPenjualanService;
    }

    public function index()
    {
        return view('retur-penjualan.index');
    }

    public function create()
    {
        return view('retur-penjualan.create');
    }

    public function show(ReturPenjualan $returPenjualan)
    {
        $returPenjualan->load(['user', 'detailReturPenjualans.barang']);

        return view('retur-penjualan.show', compact('returPenjualan'));
    }

    public function destroy(ReturPenjualan $returPenjualan)
    {
        $returPenjualan->delete();

        return redirect()->route('retur-penjualan.index')
            ->with('success', 'Data retur penjualan berhasil dihapus.');
    }
}
