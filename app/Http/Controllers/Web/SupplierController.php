<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    public function index()
    {
        return view('supplier.index');
    }
}
