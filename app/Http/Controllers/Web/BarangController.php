<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Barang;

class BarangController extends Controller
{
    public function index()
    {
        return view('barang.index');
    }

    public function trash()
    {
        return view('barang.trash');
    }

    public function history(Barang $barang)
    {
        return view('barang.history', compact('barang'));
    }

    public function show(Barang $barang)
    {
        return view('barang.show', compact('barang'));
    }
}
