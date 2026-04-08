<?php

use Livewire\Component;
use App\Models\Barang;
use App\Models\PenerimaanBarang;
use App\Models\Supplier;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public function with()
    {
        return [
            'totalBarang' => Barang::count(),
            'totalSupplier' => Supplier::count(),
            'totalPenerimaan' => PenerimaanBarang::count(),
            'stokMenipisCount' => Barang::whereColumn('stok', '<=', 'stok_minimal')->count(),
            'penerimaanTerbaru' => PenerimaanBarang::with('supplier', 'user')->latest()->take(5)->get(),
            'stokKritis' => Barang::whereColumn('stok', '<=', 'stok_minimal')->orderBy('stok', 'asc')->take(5)->get(),
            'aktivitasTerbaru' => Activity::with('causer')->latest()->take(5)->get(),
            'unreadNotifications' => Auth::user()->unreadNotifications->count(),
        ];
    }

    public function getModelName($subjectType)
    {
        if (!$subjectType) return '-';
        $parts = explode('\\', $subjectType);
        return end($parts);
    }
};
?>

<div class="p-6 space-y-8">
    <!-- Top Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Barang -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden group">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Barang</p>
                <div class="flex items-end justify-between">
                    <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalBarang) }}</h3>
                    <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stok Menipis -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden group">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 w-24 h-24 bg-red-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Stok Menipis</p>
                <div class="flex items-end justify-between">
                    <h3 class="text-3xl font-black text-red-600">{{ number_format($stokMenipisCount) }}</h3>
                    <div class="p-2 bg-red-100 rounded-lg text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Supplier -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden group">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Supplier</p>
                <div class="flex items-end justify-between">
                    <h3 class="text-3xl font-black text-slate-800">{{ number_format($totalSupplier) }}</h3>
                    <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifikasi -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden group">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 w-24 h-24 bg-amber-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Notifikasi Unread</p>
                <div class="flex items-end justify-between">
                    <h3 class="text-3xl font-black text-slate-800">{{ number_format($unreadNotifications) }}</h3>
                    <div class="p-2 bg-amber-100 rounded-lg text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Stok Kritis Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center">
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></span>
                    Stok Paling Rendah
                </h3>
                <a href="{{ route('barang.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">Manajemen Stok</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($stokKritis as $item)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 font-bold mr-3">
                            {{ substr($item->nama_barang, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ $item->nama_barang }}</p>
                            <p class="text-[10px] text-slate-400 uppercase font-bold">{{ $item->kode_barang }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-black text-red-600">{{ $item->stok }} <span class="text-[10px] text-slate-400 font-normal">{{ $item->satuan }}</span></p>
                        <p class="text-[10px] text-slate-400 font-bold italic">Min: {{ $item->stok_minimal }}</p>
                    </div>
                </div>
                @empty
                <div class="px-6 py-10 text-center">
                    <p class="text-slate-400 italic text-sm">Semua stok dalam kondisi aman.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Log Aktivitas Terbaru</h3>
                <a href="{{ route('activity.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">Lihat Semua</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($aktivitasTerbaru as $log)
                <div class="px-6 py-4 flex items-start space-x-3 hover:bg-slate-50/50 transition-colors">
                    <div class="flex-shrink-0 mt-1">
                        @php
                            $badge = match($log->event) {
                                'created' => 'bg-emerald-100 text-emerald-600',
                                'updated' => 'bg-blue-100 text-blue-600',
                                'deleted' => 'bg-red-100 text-red-600',
                                default => 'bg-slate-100 text-slate-600'
                            };
                        @endphp
                        <span class="w-2 h-2 rounded-full inline-block {{ $badge }} ring-4 ring-opacity-20 ring-current"></span>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-slate-800">
                            <span class="font-bold">{{ $log->causer->name ?? 'System' }}</span> 
                            {{ $log->event }} 
                            <span class="font-bold text-slate-500">{{ $this->getModelName($log->subject_type) }}</span>
                        </p>
                        <p class="text-[10px] text-slate-400 mt-0.5 font-medium">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="px-6 py-10 text-center">
                    <p class="text-slate-400 italic text-sm">Belum ada aktivitas.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Penerimaan Terbaru -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Transaksi Barang Masuk Terbaru</h3>
            <a href="{{ route('penerimaan.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">Daftar Penerimaan</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">No. Terima</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Supplier</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanggal</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Petugas</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($penerimaanTerbaru as $penerimaan)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 text-sm font-mono font-bold text-slate-700">{{ $penerimaan->no_terima }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 font-medium">{{ $penerimaan->supplier->nama_supplier }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $penerimaan->tgl_terima->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $penerimaan->user->name }}</td>
                        <td class="px-6 py-4">
                            @if($penerimaan->status_verifikasi == 'verified')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase">Verified</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100 uppercase">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('penerimaan.show', $penerimaan) }}" class="text-blue-600 hover:text-blue-800 font-bold text-xs uppercase tracking-tighter">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400 italic text-sm">Belum ada riwayat penerimaan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
