<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanKadaluarsaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $days;

    public function __construct($days)
    {
        $this->days = $days;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Barang::with('kategori')
            ->whereNotNull('tgl_kadaluarsa');

        if ($this->days == 'already_expired') {
            $query->where('tgl_kadaluarsa', '<', now()->toDateString());
        } else {
            $query->whereBetween('tgl_kadaluarsa', [
                now()->toDateString(),
                now()->addDays((int)$this->days)->toDateString()
            ]);
        }

        return $query->orderBy('tgl_kadaluarsa', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Nama Barang',
            'Kategori',
            'Tanggal Kadaluarsa',
            'Sisa Waktu',
        ];
    }

    public function map($barang): array
    {
        $daysDiff = (int) now()->diffInDays($barang->tgl_kadaluarsa, false);
        
        if ($daysDiff < 0) {
            $status = 'Sudah Kadaluarsa (' . abs($daysDiff) . ' hari lalu)';
        } elseif ($daysDiff == 0) {
            $status = 'Kadaluarsa HARI INI';
        } else {
            $status = $daysDiff . ' Hari Lagi';
        }

        return [
            $barang->nama_barang,
            $barang->kategori->nama_kategori ?? '-',
            $barang->tgl_kadaluarsa->format('d/m/Y'),
            $status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Laporan Barang Kadaluarsa';
    }
}
