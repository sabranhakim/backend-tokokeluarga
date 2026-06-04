<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Exports\LaporanStokExport;
use App\Exports\LaporanKadaluarsaExport;
use App\Exports\LaporanPenerimaanExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function exportStok()
    {
        return Excel::download(new LaporanStokExport, 'laporan-stok-menipis.xlsx');
    }

    public function exportKadaluarsa(Request $request)
    {
        $days = $request->query('days', 30);
        return Excel::download(new LaporanKadaluarsaExport($days), 'laporan-barang-kadaluarsa.xlsx');
    }

    public function exportPenerimaanPeriode(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        return Excel::download(new LaporanPenerimaanExport('periode', $start, $end), 'laporan-penerimaan-periode.xlsx');
    }

    public function exportPenerimaanSupplier(Request $request)
    {
        $supplierId = $request->query('supplier_id');
        return Excel::download(new LaporanPenerimaanExport('supplier', null, null, $supplierId), 'laporan-penerimaan-supplier.xlsx');
    }
}
