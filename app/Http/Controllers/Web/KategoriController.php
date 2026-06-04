<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class KategoriController extends Controller
{
    public function index()
    {
        return view('kategori.index');
    }

    public function trash()
    {
        return view('kategori.trash');
    }
}
