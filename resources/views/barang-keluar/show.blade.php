<x-app-layout>
    <x-slot name="header">
        Detail Barang Keluar #{{ $barangKeluar->no_keluar }}
    </x-slot>

    <div class="p-6">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('barang-keluar.index') }}" class="text-blue-600 hover:text-blue-700 font-medium flex items-center transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-50 pb-2">Informasi Umum</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">No. Keluar</p>
                            <p class="text-sm font-mono font-bold text-slate-900">{{ $barangKeluar->no_keluar }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tanggal Keluar</p>
                            <p class="text-sm font-medium text-slate-900">{{ $barangKeluar->tgl_keluar->format('d F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Jenis Keluar</p>
                            <p class="text-sm font-medium text-slate-900">{{ $barangKeluar->jenis_keluar_label }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Petugas</p>
                            <p class="text-sm font-medium text-slate-900">{{ $barangKeluar->user->name }}</p>
                        </div>
                        @if($barangKeluar->keterangan)
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Keterangan</p>
                            <p class="text-sm font-medium text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-100 mt-1">
                                {{ $barangKeluar->keterangan }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-lg font-bold text-slate-800">Daftar Barang Keluar</h3>
                    </div>
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/30">
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kode Barang</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Barang</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Jumlah</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">{{ $barangKeluar->jenis_keluar === 'penjualan' ? 'Harga Jual' : 'Harga Beli' }}</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @php
                                $groupedDetails = $barangKeluar->detailBarangKeluars->groupBy('barang_id');
                                $grandTotal = 0;
                                $grandTotalHarga = 0;
                            @endphp
                            @foreach($groupedDetails as $barangId => $details)
                            @php
                                $barang = $details->first()->barang;
                                $totalJumlah = $details->sum('jumlah');
                                $grandTotal += $totalJumlah;
                                $satuan = $barang->satuan;
                                $harga = $barangKeluar->jenis_keluar === 'penjualan' ? (float) $barang->harga_jual : (float) $barang->harga_beli;
                                $subtotal = $harga * $totalJumlah;
                                $grandTotalHarga += $subtotal;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs font-bold px-2 py-1 bg-slate-100 text-slate-700 rounded">{{ $barang->kode_barang }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-900">{{ $barang->nama_barang }}</div>
                                    <div class="text-xs text-slate-500">{{ $satuan }}</div>
                                    @if($details->count() > 1)
                                    <div class="mt-1 space-y-0.5">
                            @foreach($details as $d)
                            <span class="text-xs text-slate-400">Batch {{ $d->barangStok?->batch_number ?? 'N/A' }}: {{ $d->jumlah }} {{ $satuan }}</span>@if(!$loop->last), @endif
                            @endforeach
                                    </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-50 text-red-700">
                                        {{ $totalJumlah }} {{ $satuan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-slate-600">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-slate-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50/50">
                            <tr>
                                <td colspan="2" class="px-6 py-4 text-sm font-bold text-slate-600 text-right uppercase">Total Item</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-lg font-bold text-slate-900">{{ $grandTotal }}</span>
                                </td>
                                <td></td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-lg font-bold text-blue-700">Rp {{ number_format($grandTotalHarga, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
