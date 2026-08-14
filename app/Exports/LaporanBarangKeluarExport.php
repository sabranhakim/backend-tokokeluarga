<?php

namespace App\Exports;

use App\Models\BarangKeluar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanBarangKeluarExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $startDate;

    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return BarangKeluar::with(['user', 'detailBarangKeluars.barang'])
            ->when($this->startDate, fn($q) => $q->whereDate('tgl_keluar', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('tgl_keluar', '<=', $this->endDate))
            ->latest('tgl_keluar')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No. Keluar',
            'Tanggal Keluar',
            'Jenis',
            'Petugas',
            'Detail Barang',
            'Total Item',
            'Keterangan',
        ];
    }

    public function map($row): array
    {
        $details = "";
        foreach ($row->detailBarangKeluars as $detail) {
            $satuan = $detail->barang ? $detail->barang->satuan : 'pcs';
            $namaBarang = $detail->barang ? $detail->barang->nama_barang : 'Barang tidak diketahui';
            $details .= "{$namaBarang} ({$detail->jumlah} {$satuan})\n";
        }

        return [
            $row->no_keluar,
            $row->tgl_keluar->format('d/m/Y'),
            $row->jenis_keluar_label,
            $row->user->name ?? '-',
            trim($details),
            $row->detailBarangKeluars->count(),
            $row->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('E')->getAlignment()->setWrapText(true);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Laporan Barang Keluar';
    }
}