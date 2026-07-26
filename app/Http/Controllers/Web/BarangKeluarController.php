<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BarangKeluar;
use App\Services\BarangKeluarService;
use Illuminate\Http\Request;

class BarangKeluarController extends Controller
{
    protected $barangKeluarService;

    public function __construct(BarangKeluarService $barangKeluarService)
    {
        $this->barangKeluarService = $barangKeluarService;
    }

    public function index()
    {
        return view('barang-keluar.index');
    }

    public function create()
    {
        return view('barang-keluar.create');
    }

    public function show(BarangKeluar $barangKeluar)
    {
        $barangKeluar->load(['user', 'detailBarangKeluars.barang', 'detailBarangKeluars.barangStok']);
        return view('barang-keluar.show', compact('barangKeluar'));
    }

    public function trash()
    {
        return view('barang-keluar.trash');
    }

    public function destroy(BarangKeluar $barangKeluar)
    {
        $barangKeluar->delete();

        return redirect()->route('barang-keluar.index')
            ->with('success', 'Data barang keluar berhasil dihapus.');
    }

    public function restore($id)
    {
        $barangKeluar = BarangKeluar::withTrashed()->findOrFail($id);
        $barangKeluar->restore();

        return redirect()->route('trash.barang-keluar.index')
            ->with('success', 'Data barang keluar berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        $barangKeluar = BarangKeluar::withTrashed()->findOrFail($id);
        $barangKeluar->forceDelete();

        return redirect()->route('trash.barang-keluar.index')
            ->with('success', 'Data barang keluar berhasil dihapus permanen.');
    }
}
