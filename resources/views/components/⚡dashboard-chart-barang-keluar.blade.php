<?php

use Livewire\Component;
use App\Models\BarangKeluar;
use Carbon\Carbon;

new class extends Component
{
    public $filter = 'month'; // day, week, month, year

    public function mount()
    {
        $this->filter = 'month';
    }

    public function updatedFilter()
    {
        $this->dispatch('filterUpdated', $this->getChartData());
    }

    public function getChartData()
    {
        $data = [];
        $labels = [];

        if ($this->filter === 'day') {
            // Last 24 hours
            for ($i = 23; $i >= 0; $i--) {
                $time = Carbon::now()->subHours($i);
                $labels[] = $time->format('H:00');
                $data[] = BarangKeluar::whereDate('created_at', $time->toDateString())
                    ->whereRaw('HOUR(created_at) = ?', [$time->hour])
                    ->count();
            }
        } elseif ($this->filter === 'week') {
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->translatedFormat('D');
                $data[] = BarangKeluar::whereDate('tgl_keluar', $date->toDateString())->count();
            }
        } elseif ($this->filter === 'month') {
            // Last 30 days
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('d/m');
                $data[] = BarangKeluar::whereDate('tgl_keluar', $date->toDateString())->count();
            }
        } elseif ($this->filter === 'year') {
            // Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->translatedFormat('M');
                $data[] = BarangKeluar::whereMonth('tgl_keluar', $date->month)
                    ->whereYear('tgl_keluar', $date->year)
                    ->count();
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Jumlah Barang Keluar',
                    'data' => $data,
                    'borderColor' => '#f97316',
                    'backgroundColor' => 'rgba(249, 115, 22, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => '#f97316',
                ]
            ]
        ];
    }

    public function with()
    {
        return [
            'initialData' => $this->getChartData()
        ];
    }
};
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Statistik Barang Keluar</h3>
            <p class="text-xs text-slate-500">Visualisasi tren barang keluar berdasarkan waktu</p>
        </div>
        <div class="flex bg-slate-100 p-1 rounded-xl">
            <button wire:click="$set('filter', 'day')" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $filter === 'day' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Hari</button>
            <button wire:click="$set('filter', 'week')" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $filter === 'week' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Minggu</button>
            <button wire:click="$set('filter', 'month')" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $filter === 'month' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Bulan</button>
            <button wire:click="$set('filter', 'year')" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $filter === 'year' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Tahun</button>
        </div>
    </div>

    <div class="h-72 w-full" wire:ignore>
        <canvas id="barangKeluarChart"></canvas>
    </div>

    @script
    <script>
        const ctx = document.getElementById('barangKeluarChart');
        let chart = new Chart(ctx, {
            type: 'line',
            data: $wire.initialData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#94a3b8', font: { size: 10 } },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { color: '#94a3b8', font: { size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });

        $wire.on('filterUpdated', (data) => {
            chart.data = data[0];
            chart.update();
        });
    </script>
    @endscript
</div>
