<x-app-layout>
    <x-slot name="header">
        Detail Penerimaan Barang #{{ $penerimaanBarang->no_terima }}
    </x-slot>

    <div class="p-6">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('penerimaan.index') }}" class="text-blue-600 hover:text-blue-700 font-medium flex items-center transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar
            </a>
            @can('verify penerimaan')
                <div class="flex items-center space-x-3">
                    @if($penerimaanBarang->status_verifikasi == 'pending')
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                        <form action="{{ route('penerimaan.verify', $penerimaanBarang) }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            @csrf
                            @method('PATCH')
                            <div class="relative flex-1 min-w-[200px]">
                                <input type="text" name="catatan_verifikasi" placeholder="Catatan verifikasi (opsional)..." class="w-full px-4 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                            </div>
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg shadow-emerald-100 transition-all flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Verifikasi
                            </button>
                        </form>
                        <button onclick="document.getElementById('rejectModal').classList.remove('hidden')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg shadow-red-100 transition-all flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tolak
                        </button>
                    </div>
                    @elseif($penerimaanBarang->status_verifikasi == 'verified')
                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Telah Diverifikasi
                    </span>
                    @else
                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold bg-red-100 text-red-800 border border-red-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Ditolak
                    </span>
                    @endif
                </div>
            @endcan
        </div>

        @if($penerimaanBarang->supplier->trashed())
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl flex items-center">
            <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="text-sm font-bold text-red-800">Perhatian: Data Supplier Telah Dihapus</p>
                <p class="text-xs text-red-600">Supplier "{{ $penerimaanBarang->supplier->nama_supplier }}" sudah tidak aktif atau telah dihapus dari sistem.</p>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Information Card -->
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-50 pb-2">Informasi Umum</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">No. Terima</p>
                            <p class="text-sm font-mono font-bold text-slate-900">{{ $penerimaanBarang->no_terima }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tanggal Terima</p>
                            <p class="text-sm font-medium text-slate-900">{{ $penerimaanBarang->tgl_terima->format('d F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Supplier</p>
                            <p class="text-sm font-medium {{ $penerimaanBarang->supplier->trashed() ? 'text-red-600 font-bold' : 'text-slate-900' }}">
                                {{ $penerimaanBarang->supplier->nama_supplier }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Petugas Penerima</p>
                            <p class="text-sm font-medium text-slate-900">{{ $penerimaanBarang->user->name }}</p>
                        </div>
                        @if(in_array($penerimaanBarang->status_verifikasi, ['verified', 'rejected']))
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Catatan Verifikasi</p>
                            <p class="text-sm font-medium text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-100 mt-1">
                                {{ $penerimaanBarang->catatan_verifikasi ?: 'Tidak ada catatan' }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                @if($penerimaanBarang->foto_bon)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-50 pb-2">Foto Bon</h3>
                    <div class="rounded-xl overflow-hidden border border-slate-100 shadow-inner bg-slate-50">
                        <img src="{{ $penerimaanBarang->foto_bon }}" alt="Foto Bon" class="w-full h-auto cursor-pointer hover:scale-105 transition-transform duration-300" onclick="window.open(this.src)">
                    </div>
                </div>
                @endif
            </div>

            <!-- Items Table Card -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-lg font-bold text-slate-800">Daftar Barang Diterima</h3>
                    </div>
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/30">
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kode Barang</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Barang</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Batch</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kadaluarsa</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($penerimaanBarang->detailPenerimaans as $detail)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs font-bold px-2 py-1 bg-slate-100 text-slate-700 rounded">{{ $detail->barang->kode_barang }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-900">{{ $detail->barang->nama_barang }}</div>
                                    <div class="text-xs text-slate-500">{{ $detail->barang->satuan }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs text-slate-600">{{ $detail->batch_number ?: '-' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs text-slate-600">{{ $detail->tgl_kadaluarsa ? $detail->tgl_kadaluarsa->format('d/m/Y') : '-' }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-50 text-blue-700">
                                        {{ $detail->jumlah }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/50">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-sm font-bold text-slate-600 text-right uppercase">Total Item</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-lg font-bold text-slate-900">{{ $penerimaanBarang->detailPenerimaans->sum('jumlah') }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
<div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800">Tolak Penerimaan Barang</h3>
                <button onclick="document.getElementById('rejectModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-slate-600 mb-6">
                Anda akan menolak penerimaan barang <strong>#{{ $penerimaanBarang->no_terima }}</strong>.
                Tuliskan alasan penolakan di bawah ini.
            </p>
            <form action="{{ route('penerimaan.reject', $penerimaanBarang) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="catatan_verifikasi" class="block text-sm font-semibold text-slate-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="catatan_verifikasi" id="catatan_verifikasi" rows="4" required
                        class="w-full px-4 py-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all resize-none"
                        placeholder="Contoh: Barang tidak sesuai dengan pesanan..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-lg shadow-red-100">
                        Ya, Tolak Penerimaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-app-layout>
