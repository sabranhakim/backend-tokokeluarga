<x-app-layout>
    <x-slot name="header">
        FAQ / Bantuan
    </x-slot>

    <div class="space-y-6 pb-8">
        <!-- Hero -->
        <div class="bg-gradient-to-r from-blue-700 to-blue-500 rounded-2xl p-6 lg:p-8 text-white shadow-sm">
            <h2 class="text-2xl font-extrabold tracking-tight">Pusat Bantuan &amp; FAQ</h2>
            <p class="mt-2 text-blue-100 text-sm max-w-2xl leading-relaxed">
                Kumpulan pertanyaan yang sering diajukan beserta panduan singkat untuk membantu Anda
                mengelola stok, transaksi, dan laporan di sistem Grosir Toko Keluarga.
            </p>
            <div class="mt-5 flex flex-wrap gap-2">
                <a href="#pengelolaan-stok" class="px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur text-sm font-semibold transition-colors">Stok</a>
                <a href="#transaksi" class="px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur text-sm font-semibold transition-colors">Transaksi</a>
                <a href="#laporan" class="px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur text-sm font-semibold transition-colors">Laporan</a>
                <a href="#akun-dan-role" class="px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur text-sm font-semibold transition-colors">Akun &amp; Role</a>
                <a href="#umum" class="px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur text-sm font-semibold transition-colors">Umum</a>
            </div>
        </div>

        @php
            $groups = [
                'pengelolaan-stok' => [
                    'title' => 'Pengelolaan Stok',
                    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                    'items' => [
                        [
                            'q' => 'Bagaimana cara menambahkan barang baru?',
                            'a' => 'Buka menu <strong>Stok Barang</strong> lalu klik tombol <strong>Tambah Barang</strong>. Isi nama, kategori, unit, dan harga dasar, kemudian simpan. Barang baru otomatis tersedia untuk penerimaan barang.',
                        ],
                        [
                            'q' => 'Bagaimana cara menambah stok barang (penerimaan)?',
                            'a' => 'Buka menu <strong>Penerimaan Barang</strong> &gt; <strong>Buat Penerimaan</strong>. Pilih supplier, lalu tambahkan barang beserta jumlah, harga beli, dan tanggal kedaluwarsa. Simpan lalu <strong>Verifikasi</strong> agar stok masuk dan riwayat stok tercatat.',
                        ],
                        [
                            'q' => 'Apa itu "Stok per Batch"?',
                            'a' => 'Stok disimpan berdasarkan <strong>batch</strong> (per kedatangan). Menu <strong>Stok per Batch</strong> menampilkan setiap batch barang lengkap dengan tanggal kedaluwarsa, harga beli, dan jumlah tersisa. Ini membantu melacak barang mana yang lebih dulu harus dijual (FIFO).',
                        ],
                        [
                            'q' => 'Bagaimana jika barang sudah kedaluwarsa atau stok menipis?',
                            'a' => 'Halaman <strong>Laporan</strong> memiliki tab <strong>Stok Menipis</strong> dan <strong>Kadaluarsa</strong>. Barang dengan stok di bawah batas minimum atau mendekati tanggal kedaluwarsa akan tampil di sana, dan dapat di-ekspor ke Excel.',
                        ],
                        [
                            'q' => 'Apa fungsi stock opname?',
                            'a' => 'Stock opname adalah penghitungan fisik stok untuk mencocokkan stok di sistem dengan kondisi nyata. Buka <strong>Stock Opname</strong> &gt; <strong>Buat Opname</strong>, isi jumlah hasil hitung, lalu <strong>Finalisasi</strong>. Selisih akan otomatis menyesuaikan stok dan tercatat di riwayat.',
                        ],
                    ],
                ],
                'transaksi' => [
                    'title' => 'Transaksi Barang',
                    'icon' => 'M3 10h18M7 15h2m4 0h6M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'items' => [
                        [
                            'q' => 'Bagaimana cara mencatat barang keluar (penjualan)?',
                            'a' => 'Buka menu <strong>Barang Keluar</strong> &gt; <strong>Buat Barang Keluar</strong>. Pilih jenis transaksi (penjualan, rusak, dll.), pilih barang dan jumlah, lalu simpan. Stok otomatis berkurang.',
                        ],
                        [
                            'q' => 'Bagaimana cara membuat retur pembelian (mengembalikan barang ke supplier)?',
                            'a' => 'Buka <strong>Retur Pembelian</strong> &gt; <strong>Buat Retur</strong>. Pilih penerimaan asal, tentukan barang dan jumlah yang dikembalikan beserta alasan, lalu simpan. Stok akan berkurang sesuai retur.',
                        ],
                        [
                            'q' => 'Bagaimana cara membuat retur penjualan (barang dikembalikan pelanggan)?',
                            'a' => 'Buka <strong>Retur Penjualan</strong> &gt; <strong>Buat Retur</strong>. Pilih barang keluar asal, barang dan jumlah yang dikembalikan beserta alasan, lalu simpan. Stok akan kembali bertambah.',
                        ],
                        [
                            'q' => 'Apakah transaksi yang sudah dibuat bisa dihapus?',
                            'a' => 'Ya. Transaksi dengan status belum diverifikasi (khusus penerimaan) dapat dihapus dari halaman daftar. Setelah diverifikasi, data dihapus melalui menu <strong>Trash</strong> (Restore / Hapus Permanen) oleh pengguna dengan hak akses sesuai.',
                        ],
                        [
                            'q' => 'Mengapa transaksi harus diverifikasi?',
                            'a' => 'Verifikasi adalah tanda bahwa data sudah diperiksa dan dianggap sah. Pada <strong>Penerimaan Barang</strong>, stok baru akan masuk ke sistem <strong>setelah</strong> penerimaan diverifikasi, agar stok tidak bertambah sebelum data dipastikan benar.',
                        ],
                    ],
                ],
                'laporan' => [
                    'title' => 'Laporan &amp; Ekspor',
                    'icon' => 'M9 17v-2a4 4 0 00-4-4H5m14 0h-2a4 4 0 00-4 4v2m-2 4h.01M9 21h6a2 2 0 002-2V5a2 2 0 00-2-2H9a2 2 0 00-2-2H9a2 2 0 00-2 2v14a2 2 0 002 2z',
                    'items' => [
                        [
                            'q' => 'Laporan apa saja yang tersedia?',
                            'a' => 'Tersedia laporan <strong>Stok Menipis</strong>, <strong>Barang Kadaluarsa</strong>, <strong>Penerimaan per Periode</strong>, dan <strong>Penerimaan per Supplier</strong>. Semua dapat dilihat di menu <strong>Laporan</strong>.',
                        ],
                        [
                            'q' => 'Bagaimana cara meng-ekspor laporan ke Excel?',
                            'a' => 'Buka menu <strong>Laporan</strong>, pilih tab yang diinginkan, lalu klik tombol <strong>Export Excel</strong>. File akan terunduh otomatis dalam format .xlsx yang siap dibuka di Excel atau Google Sheets.',
                        ],
                        [
                            'q' => 'Bisakah laporan di-filter berdasarkan tanggal?',
                            'a' => 'Ya. Pada laporan <strong>Penerimaan per Periode</strong>, Anda dapat memilih rentang tanggal awal dan akhir untuk mempersempit data sebelum di-ekspor.',
                        ],
                    ],
                ],
                'akun-dan-role' => [
                    'title' => 'Akun &amp; Role Pengguna',
                    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                    'items' => [
                        [
                            'q' => 'Bagaimana cara menambahkan pengguna baru?',
                            'a' => 'Buka <strong>Manajemen User</strong> (khusus Administrator) &gt; <strong>Tambah User</strong>. Isi nama, email, password, dan status aktif, lalu simpan. Email tersebut dapat langsung dipakai untuk login.',
                        ],
                        [
                            'q' => 'Apa perbedaan role Admin, Staff, dan Owner?',
                            'a' => '<strong>Admin</strong> memiliki akses penuh termasuk manajemen user, role, dan trash. <strong>Staff</strong> dapat melakukan operasional (tambah/ubah/verifikasi transaksi) tanpa akses pengaturan sistem. <strong>Owner</strong> hanya dapat melihat data, laporan, dan log aktivitas (read-only) untuk memantau jalannya usaha.',
                        ],
                        [
                            'q' => 'Bagaimana cara mengatur izin (permission) sebuah role?',
                            'a' => 'Buka <strong>Manajemen Role</strong> (khusus Administrator), pilih role yang ingin diubah, lalu centang izin yang sesuai pada form. Simpan untuk menerapkan perubahan. Perubahan langsung berlaku bagi seluruh user dengan role tersebut.',
                        ],
                        [
                            'q' => 'Bagaimana jika user terkunci atau lupa password?',
                            'a' => 'Hubungi Administrator. Admin dapat membuka <strong>Manajemen User</strong>, mengedit user tersebut, dan mengatur ulang password baru.',
                        ],
                    ],
                ],
                'umum' => [
                    'title' => 'Umum',
                    'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'items' => [
                        [
                            'q' => 'Apa itu Log Aktivitas dan bagaimana menggunakannya?',
                            'a' => '<strong>Log Aktivitas</strong> mencatat setiap aksi penting (tambah, ubah, hapus, verifikasi, login) beserta pengguna, waktu, dan detail perubahan. Gunakan untuk audit dan menelusuri penyebab perubahan data.',
                        ],
                        [
                            'q' => 'Apa yang dimaksud menu Trash?',
                            'a' => '<strong>Trash</strong> menampung data yang dihapus sementara. Data di sana dapat di-<strong>Restore</strong> (dikembalikan) atau di-<strong>Hapus Permanen</strong>. Halaman ini hanya dapat diakses oleh Administrator.',
                        ],
                        [
                            'q' => 'Bagaimana cara melihat riwayat stok suatu barang?',
                            'a' => 'Buka <strong>Stok Barang</strong>, pilih barang, lalu klik ikon riwayat untuk melihat pergerakan stok (masuk, keluar, retur, opname). Anda juga dapat membuka <strong>Riwayat Stok</strong> untuk melihat semua pergerakan sekaligus.',
                        ],
                        [
                            'q' => 'Apa yang harus dilakukan jika data tampak salah?',
                            'a' => 'Cek <strong>Log Aktivitas</strong> untuk melihat siapa yang mengubah data dan kapan. Jika diperlukan, perbaiki via form edit, atau hubungi Administrator untuk memulihkan data dari Trash.',
                        ],
                    ],
                ],
            ];
        @endphp

        <div class="space-y-8" id="faq-groups">
            @foreach ($groups as $groupKey => $group)
                <section id="{{ $groupKey }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-50">
                        <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $group['icon'] }}"/></svg>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-800">{{ $group['title'] }}</h3>
                    </div>
                    <div class="divide-y divide-slate-50">
                        @foreach ($group['items'] as $index => $item)
                            <div x-data="{ open: false }" class="px-6">
                                <button type="button" @click="open = !open"
                                    class="w-full flex items-center justify-between gap-4 py-4 text-left group">
                                    <span class="text-sm font-bold text-slate-700 group-hover:text-blue-600 transition-colors">{{ $item['q'] }}</span>
                                    <svg class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180 text-blue-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-1">
                                    <p class="pb-4 text-sm text-slate-500 leading-relaxed">{!! $item['a'] !!}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <!-- Contact -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-extrabold text-slate-800">Masih ada pertanyaan?</h3>
                <p class="text-sm text-slate-500 mt-1">Hubungi Administrator atau cek <strong>Log Aktivitas</strong> untuk memahami alur sistem lebih lanjut.</p>
            </div>
            <a href="{{ route('activity.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Lihat Log Aktivitas
            </a>
        </div>
    </div>
</x-app-layout>
