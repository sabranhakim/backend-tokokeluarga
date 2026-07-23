<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class BarangStokController extends Controller
{
    public function index()
    {
        return view('barang-stok.index');
    }
}
