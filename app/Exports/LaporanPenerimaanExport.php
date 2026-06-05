<?php

namespace App\Exports;

use App\Models\PenerimaanBarang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPenerimaanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $supplierId;
    protected $month;
    protected $year;
    protected $type;

    public function __construct($type, $startDate = null, $endDate = null, $supplierId = null, $month = null, $year = null)
    {
        $this->type = $type;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->supplierId = $supplierId;
        $this->month = $month;
        $this->year = $year;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = PenerimaanBarang::with(['supplier', 'detailPenerimaans.barang']);

        if ($this->type === 'periode') {
            $query->whereBetween('tgl_terima', [$this->startDate, $this->endDate]);
        } elseif ($this->type === 'supplier') {
            $query->where('supplier_id', $this->supplierId);
            
            if ($this->month) {
                $query->whereMonth('tgl_terima', $this->month);
            }

            if ($this->year) {
                $query->whereYear('tgl_terima', $this->year);
            }
        }

        return $query->latest('tgl_terima')->get();
    }

    public function headings(): array
    {
        return [
            'No. Penerimaan',
            'Tanggal Terima',
            'Supplier',
            'Detail Barang',
            'Total Item',
            'Status',
        ];
    }

    public function map($row): array
    {
        $details = "";
        foreach($row->detailPenerimaans as $detail) {
            $details .= "{$detail->barang->nama_barang} ({$detail->jumlah} {$detail->barang->satuan})\n";
        }

        return [
            $row->no_terima,
            $row->tgl_terima->format('d/m/Y'),
            $row->supplier->nama_supplier,
            trim($details),
            $row->detailPenerimaans->count(),
            ucfirst($row->status_verifikasi),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('D')->getAlignment()->setWrapText(true);
        
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return $this->type === 'periode' ? 'Laporan Penerimaan Periode' : 'Laporan Penerimaan Per Supplier';
    }
}
