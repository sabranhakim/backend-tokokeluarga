<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanBarang;
use Illuminate\Http\Request;

class PenerimaanBarangController extends Controller
{
    public function index()
    {
        $penerimaans = PenerimaanBarang::with('supplier', 'user')->latest()->paginate(10);
        return view('penerimaan.index', compact('penerimaans'));
    }

    public function show(PenerimaanBarang $penerimaanBarang)
    {
        $penerimaanBarang->load('detailPenerimaans.barang', 'supplier', 'user');
        return view('penerimaan.show', compact('penerimaanBarang'));
    }

    public function verify(PenerimaanBarang $penerimaanBarang)
    {
        $penerimaanBarang->update(['status_verifikasi' => 'verified']);

        return redirect()->back()
            ->with('success', 'Penerimaan barang berhasil diverifikasi.');
    }

    public function destroy(PenerimaanBarang $penerimaanBarang)
    {
        // Should we delete? Usually, we might just cancel or keep for history.
        // But for a basic CRUD, we can provide delete.
        $penerimaanBarang->delete();

        return redirect()->route('penerimaan.index')
            ->with('success', 'Data penerimaan berhasil dihapus.');
    }
}
