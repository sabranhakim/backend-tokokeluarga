<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanBarang;

class PenerimaanBarangController extends Controller
{
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
        $penerimaanBarang->update(['status_verifikasi' => 'verified']);

        return redirect()->back()
            ->with('success', 'Penerimaan barang berhasil diverifikasi.');
    }

    public function destroy(PenerimaanBarang $penerimaanBarang)
    {
        $penerimaanBarang->delete();

        return redirect()->route('penerimaan.index')
            ->with('success', 'Data penerimaan berhasil dihapus.');
    }
}
