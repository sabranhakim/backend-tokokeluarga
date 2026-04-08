<?php

use Livewire\Component;
use App\Models\PenerimaanBarang;
use Illuminate\Support\Facades\DB;
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
                $data[] = PenerimaanBarang::whereDate('created_at', $time->toDateString())
                    ->whereRaw('HOUR(created_at) = ?', [$time->hour])
                    ->count();
            }
        } elseif ($this->filter === 'week') {
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->translatedFormat('D');
                $data[] = PenerimaanBarang::whereDate('tgl_terima', $date->toDateString())->count();
            }
        } elseif ($this->filter === 'month') {
            // Last 30 days
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('d/m');
                $data[] = PenerimaanBarang::whereDate('tgl_terima', $date->toDateString())->count();
            }
        } elseif ($this->filter === 'year') {
            // Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->translatedFormat('M');
                $data[] = PenerimaanBarang::whereMonth('tgl_terima', $date->month)
                    ->whereYear('tgl_terima', $date->year)
                    ->count();
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Jumlah Penerimaan',
                    'data' => $data,
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => '#2563eb',
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
            <h3 class="text-lg font-bold text-slate-800">Statistik Penerimaan Barang</h3>
            <p class="text-xs text-slate-500">Visualisasi tren barang masuk berdasarkan waktu</p>
        </div>
        <div class="flex bg-slate-100 p-1 rounded-xl">
            <button wire:click="$set('filter', 'day')" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $filter === 'day' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Hari</button>
            <button wire:click="$set('filter', 'week')" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $filter === 'week' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Minggu</button>
            <button wire:click="$set('filter', 'month')" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $filter === 'month' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Bulan</button>
            <button wire:click="$set('filter', 'year')" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $filter === 'year' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Tahun</button>
        </div>
    </div>

    <div class="h-72 w-full" wire:ignore>
        <canvas id="penerimaanChart"></canvas>
    </div>

    @script
    <script>
        const ctx = document.getElementById('penerimaanChart');
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
