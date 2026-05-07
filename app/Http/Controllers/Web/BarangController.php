<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

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
}
