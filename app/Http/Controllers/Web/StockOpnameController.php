<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StockOpname;
use App\Services\StockOpnameService;

class StockOpnameController extends Controller
{
    protected $stockOpnameService;

    public function __construct(StockOpnameService $stockOpnameService)
    {
        $this->stockOpnameService = $stockOpnameService;
    }

    public function index()
    {
        return view('stock-opname.index');
    }

    public function create()
    {
        return view('stock-opname.create');
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load(['user', 'detailStockOpnames.barang']);

        return view('stock-opname.show', compact('stockOpname'));
    }

    public function destroy(StockOpname $stockOpname)
    {
        $stockOpname->delete();

        return redirect()->route('stock-opname.index')
            ->with('success', 'Data stock opname berhasil dihapus.');
    }
}
