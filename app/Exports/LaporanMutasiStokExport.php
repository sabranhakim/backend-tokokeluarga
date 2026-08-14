<?php

namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanMutasiStokExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $startDate;

    protected $endDate;

    protected $type;

    public function __construct($startDate = null, $endDate = null, $type = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->type = $type;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return StockMovement::with(['barang', 'user'])
            ->when($this->startDate, fn($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->when(in_array($this->type, ['in', 'out']), fn($q) => $q->where('type', $this->type))
            ->latest('created_at')
            ->limit(5000)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Barang',
            'Tipe',
            'Jumlah',
            'Stok Sebelum',
            'Stok Setelah',
            'Alasan',
            'Petugas',
        ];
    }

    public function map($row): array
    {
        return [
            $row->created_at->format('d/m/Y H:i'),
            $row->barang->nama_barang ?? 'Barang tidak diketahui',
            $row->type === 'in' ? 'Masuk' : 'Keluar',
            $row->quantity,
            $row->before_quantity,
            $row->after_quantity,
            $row->reason ?? '-',
            $row->user->name ?? '-',
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
        return 'Laporan Mutasi Stok';
    }
}