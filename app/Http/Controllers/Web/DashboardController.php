<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\PenerimaanBarang;
use App\Models\Supplier;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBarang = Barang::count();
        $totalSupplier = Supplier::count();
        $totalPenerimaan = PenerimaanBarang::count();
        $penerimaanTerbaru = PenerimaanBarang::with('supplier', 'user')->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalBarang',
            'totalSupplier',
            'totalPenerimaan',
            'penerimaanTerbaru'
        ));
    }
}
