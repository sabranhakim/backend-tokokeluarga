<?php

use Livewire\Component;
use App\Models\Barang;
use App\Models\PenerimaanBarang;
use App\Models\Supplier;

new class extends Component
{
    public function with()
    {
        return [
            'totalBarang' => Barang::count(),
            'totalSupplier' => Supplier::count(),
            'totalPenerimaan' => PenerimaanBarang::count(),
            'penerimaanTerbaru' => PenerimaanBarang::with('supplier', 'user')->latest()->take(5)->get(),
        ];
    }
};
?>

<div class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Stat Cards -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center transition-all hover:shadow-md">
            <div class="rounded-full bg-blue-50 p-4 mr-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Barang</p>
                <p class="text-3xl font-bold text-slate-900">{{ $totalBarang }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center transition-all hover:shadow-md">
            <div class="rounded-full bg-emerald-50 p-4 mr-4">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Supplier</p>
                <p class="text-3xl font-bold text-slate-900">{{ $totalSupplier }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center transition-all hover:shadow-md">
            <div class="rounded-full bg-amber-50 p-4 mr-4">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Penerimaan Barang</p>
                <p class="text-3xl font-bold text-slate-900">{{ $totalPenerimaan }}</p>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-slate-800">Penerimaan Barang Terbaru</h3>
            <a href="{{ route('penerimaan.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">ID Penerimaan</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Petugas</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($penerimaanTerbaru as $penerimaan)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">#{{ str_pad($penerimaan->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $penerimaan->supplier->nama_supplier }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $penerimaan->tanggal_penerimaan->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $penerimaan->user->name }}</td>
                        <td class="px-6 py-4">
                            @if($penerimaan->is_verified)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Verified</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('penerimaan.show', $penerimaan) }}" class="text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500 italic">Belum ada riwayat penerimaan barang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>