<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangStok;
use App\Models\DetailBarangKeluar;
use App\Models\DetailPenerimaan;
use App\Models\Kategori;
use App\Models\PenerimaanBarang;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        StockMovement::truncate();
        DetailBarangKeluar::truncate();
        DetailPenerimaan::truncate();
        BarangStok::truncate();
        BarangKeluar::truncate();
        Barang::truncate();
        PenerimaanBarang::truncate();
        Kategori::truncate();
        Supplier::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $permissions = [
            'view dashboard', 'manage users', 'manage roles',
            'view barang', 'manage barang',
            'view supplier', 'manage supplier',
            'view kategori', 'manage kategori',
            'view penerimaan', 'create penerimaan', 'verify penerimaan', 'delete penerimaan',
            'view barang_keluar', 'create barang_keluar', 'delete barang_keluar',
            'view trash', 'manage trash',
            'manage laporan', 'manage activity',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->givePermissionTo([
            'view dashboard', 'view barang', 'manage barang',
            'view supplier', 'manage supplier',
            'view kategori', 'manage kategori',
            'view penerimaan', 'create penerimaan',
            'view barang_keluar', 'create barang_keluar',
            'manage laporan',
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@tokokeluarga.com'],
            ['name' => 'Administrator', 'password' => Hash::make('password123')]
        );
        $admin->assignRole($adminRole);

        $staff = User::firstOrCreate(
            ['email' => 'staff@tokokeluarga.com'],
            ['name' => 'Staff Gudang', 'password' => Hash::make('password123')]
        );
        $staff->assignRole($staffRole);

        $kategoriList = [
            ['nama_kategori' => 'Tepung & Gandum', 'barangs' => [
                ['nama_barang' => 'Tepung Terigu Segitiga Biru', 'satuan' => 'Karung', 'isi' => 25, 'harga_beli' => 130000, 'harga_jual' => 145000, 'stok_minimal' => 5],
                ['nama_barang' => 'Tepung Terigu Cakra Kembar', 'satuan' => 'Karung', 'isi' => 25, 'harga_beli' => 140000, 'harga_jual' => 155000, 'stok_minimal' => 5],
                ['nama_barang' => 'Tepung Beras Rose Brand', 'satuan' => 'Karung', 'isi' => 10, 'harga_beli' => 95000, 'harga_jual' => 110000, 'stok_minimal' => 3],
                ['nama_barang' => 'Tepung Tapioka Gunung Agung', 'satuan' => 'Karung', 'isi' => 25, 'harga_beli' => 115000, 'harga_jual' => 130000, 'stok_minimal' => 5],
                ['nama_barang' => 'Gandum Utuh', 'satuan' => 'Kg', 'isi' => 1, 'harga_beli' => 12000, 'harga_jual' => 15000, 'stok_minimal' => 20],
            ]],
            ['nama_kategori' => 'Gula & Pemanis', 'barangs' => [
                ['nama_barang' => 'Gula Pasir Gulaku', 'satuan' => 'Karung', 'isi' => 50, 'harga_beli' => 650000, 'harga_jual' => 700000, 'stok_minimal' => 3],
                ['nama_barang' => 'Gula Halus', 'satuan' => 'Karung', 'isi' => 25, 'harga_beli' => 340000, 'harga_jual' => 370000, 'stok_minimal' => 3],
                ['nama_barang' => 'Gula Merah Aren', 'satuan' => 'Kg', 'isi' => 1, 'harga_beli' => 22000, 'harga_jual' => 28000, 'stok_minimal' => 10],
                ['nama_barang' => 'Madu Murni', 'satuan' => 'Botol', 'isi' => 1, 'harga_beli' => 45000, 'harga_jual' => 55000, 'stok_minimal' => 10],
            ]],
            ['nama_kategori' => 'Minyak & Lemak', 'barangs' => [
                ['nama_barang' => 'Minyak Goreng Sania', 'satuan' => 'Dus', 'isi' => 12, 'harga_beli' => 180000, 'harga_jual' => 205000, 'stok_minimal' => 5],
                ['nama_barang' => 'Minyak Goreng Bimoli', 'satuan' => 'Dus', 'isi' => 12, 'harga_beli' => 230000, 'harga_jual' => 260000, 'stok_minimal' => 5],
                ['nama_barang' => 'Mentega Blue Band', 'satuan' => 'Dus', 'isi' => 24, 'harga_beli' => 280000, 'harga_jual' => 310000, 'stok_minimal' => 3],
                ['nama_barang' => 'Butter Anchor', 'satuan' => 'Dus', 'isi' => 12, 'harga_beli' => 350000, 'harga_jual' => 390000, 'stok_minimal' => 3],
                ['nama_barang' => 'Shortening', 'satuan' => 'Karung', 'isi' => 15, 'harga_beli' => 320000, 'harga_jual' => 355000, 'stok_minimal' => 3],
            ]],
            ['nama_kategori' => 'Bumbu & Rempah', 'barangs' => [
                ['nama_barang' => 'Garam Halus Refina', 'satuan' => 'Karung', 'isi' => 25, 'harga_beli' => 50000, 'harga_jual' => 65000, 'stok_minimal' => 5],
                ['nama_barang' => 'Vanili Bubuk', 'satuan' => 'Botol', 'isi' => 1, 'harga_beli' => 15000, 'harga_jual' => 20000, 'stok_minimal' => 20],
                ['nama_barang' => 'Baking Powder Koepoe', 'satuan' => 'Dus', 'isi' => 24, 'harga_beli' => 85000, 'harga_jual' => 105000, 'stok_minimal' => 5],
                ['nama_barang' => 'Soda Kue', 'satuan' => 'Dus', 'isi' => 24, 'harga_beli' => 75000, 'harga_jual' => 95000, 'stok_minimal' => 5],
                ['nama_barang' => 'Cinnamon Powder', 'satuan' => 'Botol', 'isi' => 1, 'harga_beli' => 18000, 'harga_jual' => 25000, 'stok_minimal' => 10],
            ]],
            ['nama_kategori' => 'Cokelat & Selai', 'barangs' => [
                ['nama_barang' => 'Cokelat Batang Tulip', 'satuan' => 'Dus', 'isi' => 20, 'harga_beli' => 280000, 'harga_jual' => 320000, 'stok_minimal' => 3],
                ['nama_barang' => 'Cokelat Bubuk Van Houten', 'satuan' => 'Dus', 'isi' => 12, 'harga_beli' => 240000, 'harga_jual' => 275000, 'stok_minimal' => 3],
                ['nama_barang' => 'Selai Strawberry', 'satuan' => 'Dus', 'isi' => 12, 'harga_beli' => 150000, 'harga_jual' => 180000, 'stok_minimal' => 5],
                ['nama_barang' => 'Selai Nanas', 'satuan' => 'Dus', 'isi' => 12, 'harga_beli' => 130000, 'harga_jual' => 160000, 'stok_minimal' => 5],
                ['nama_barang' => 'Pasta Cokelat', 'satuan' => 'Botol', 'isi' => 1, 'harga_beli' => 25000, 'harga_jual' => 35000, 'stok_minimal' => 10],
            ]],
            ['nama_kategori' => 'Susu & Keju', 'barangs' => [
                ['nama_barang' => 'Susu Cair UHT', 'satuan' => 'Dus', 'isi' => 12, 'harga_beli' => 140000, 'harga_jual' => 165000, 'stok_minimal' => 5],
                ['nama_barang' => 'Susu Bubuk Full Cream', 'satuan' => 'Kaleng', 'isi' => 1, 'harga_beli' => 35000, 'harga_jual' => 45000, 'stok_minimal' => 15],
                ['nama_barang' => 'Keju Cheddar Craft', 'satuan' => 'Dus', 'isi' => 24, 'harga_beli' => 380000, 'harga_jual' => 420000, 'stok_minimal' => 3],
                ['nama_barang' => 'Keju Mozzarella', 'satuan' => 'Kg', 'isi' => 1, 'harga_beli' => 85000, 'harga_jual' => 105000, 'stok_minimal' => 5],
                ['nama_barang' => 'Whipped Cream', 'satuan' => 'Dus', 'isi' => 12, 'harga_beli' => 210000, 'harga_jual' => 245000, 'stok_minimal' => 3],
            ]],
            ['nama_kategori' => 'Telur & Daging', 'barangs' => [
                ['nama_barang' => 'Telur Ayam Negeri', 'satuan' => 'Papan', 'isi' => 30, 'harga_beli' => 46000, 'harga_jual' => 53000, 'stok_minimal' => 10],
                ['nama_barang' => 'Telur Bebek', 'satuan' => 'Papan', 'isi' => 30, 'harga_beli' => 55000, 'harga_jual' => 65000, 'stok_minimal' => 5],
                ['nama_barang' => 'Daging Sapi Giling', 'satuan' => 'Kg', 'isi' => 1, 'harga_beli' => 90000, 'harga_jual' => 110000, 'stok_minimal' => 10],
                ['nama_barang' => 'Daging Ayam Fillet', 'satuan' => 'Kg', 'isi' => 1, 'harga_beli' => 32000, 'harga_jual' => 40000, 'stok_minimal' => 15],
            ]],
            ['nama_kategori' => 'Plastik & Packaging', 'barangs' => [
                ['nama_barang' => 'Plastik PE Ukuran 1/4 Kg', 'satuan' => 'Pak', 'isi' => 100, 'harga_beli' => 12000, 'harga_jual' => 18000, 'stok_minimal' => 30],
                ['nama_barang' => 'Plastik PE Ukuran 1/2 Kg', 'satuan' => 'Pak', 'isi' => 100, 'harga_beli' => 15000, 'harga_jual' => 22000, 'stok_minimal' => 30],
                ['nama_barang' => 'Kotak Kue Ukuran 20 cm', 'satuan' => 'Pak', 'isi' => 50, 'harga_beli' => 35000, 'harga_jual' => 45000, 'stok_minimal' => 10],
                ['nama_barang' => 'Paper Bag Kecil', 'satuan' => 'Pak', 'isi' => 50, 'harga_beli' => 18000, 'harga_jual' => 25000, 'stok_minimal' => 20],
                ['nama_barang' => 'Pita Kue', 'satuan' => 'Roll', 'isi' => 1, 'harga_beli' => 5000, 'harga_jual' => 10000, 'stok_minimal' => 50],
            ]],
            ['nama_kategori' => 'Peralatan Bakery', 'barangs' => [
                ['nama_barang' => 'Loyang Bulat 22 cm', 'satuan' => 'Pcs', 'isi' => 1, 'harga_beli' => 25000, 'harga_jual' => 35000, 'stok_minimal' => 10],
                ['nama_barang' => 'Loyang Persegi 20x20', 'satuan' => 'Pcs', 'isi' => 1, 'harga_beli' => 22000, 'harga_jual' => 30000, 'stok_minimal' => 10],
                ['nama_barang' => 'Whisk (Kocokan)', 'satuan' => 'Pcs', 'isi' => 1, 'harga_beli' => 15000, 'harga_jual' => 22000, 'stok_minimal' => 15],
                ['nama_barang' => 'Spatula Silikon', 'satuan' => 'Pcs', 'isi' => 1, 'harga_beli' => 12000, 'harga_jual' => 18000, 'stok_minimal' => 20],
                ['nama_barang' => 'Kertas Roti', 'satuan' => 'Roll', 'isi' => 1, 'harga_beli' => 8000, 'harga_jual' => 12000, 'stok_minimal' => 30],
            ]],
            ['nama_kategori' => 'Minuman', 'barangs' => [
                ['nama_barang' => 'Kopi Bubuk Kapal Api', 'satuan' => 'Dus', 'isi' => 24, 'harga_beli' => 180000, 'harga_jual' => 210000, 'stok_minimal' => 5],
                ['nama_barang' => 'Teh Celup Sosro', 'satuan' => 'Dus', 'isi' => 100, 'harga_beli' => 35000, 'harga_jual' => 45000, 'stok_minimal' => 10],
                ['nama_barang' => 'Sirup Marjan', 'satuan' => 'Dus', 'isi' => 12, 'harga_beli' => 160000, 'harga_jual' => 190000, 'stok_minimal' => 5],
                ['nama_barang' => 'Air Mineral 600ml', 'satuan' => 'Dus', 'isi' => 24, 'harga_beli' => 30000, 'harga_jual' => 40000, 'stok_minimal' => 15],
            ]],
        ];

        $createdKategoris = collect();
        foreach ($kategoriList as $kData) {
            $createdKategoris->push(Kategori::create(['nama_kategori' => $kData['nama_kategori']]));
        }

        $suppliers = Supplier::factory(10)->create();

        $barangs = collect();
        $kodeCounter = 1000;

        foreach ($kategoriList as $i => $kData) {
            $kategori = $createdKategoris[$i];

            foreach ($kData['barangs'] as $bData) {
                $barang = Barang::create([
                    'kode_barang' => 'BRG-' . $kodeCounter,
                    'nama_barang' => $bData['nama_barang'],
                    'kategori_id' => $kategori->id,
                    'supplier_id' => $suppliers->random()->id,
                    'satuan' => $bData['satuan'],
                    'isi' => $bData['isi'],
                    'harga_beli' => $bData['harga_beli'],
                    'harga_jual' => $bData['harga_jual'],
                    'stok' => 0,
                    'stok_minimal' => $bData['stok_minimal'],
                ]);
                $barangs->push($barang);
                $kodeCounter++;
            }
        }

        $penerimaanItems = [
            ['supplier_idx' => 0, 'tgl' => '-3 months', 'details' => [
                ['barang_idx' => 0, 'jumlah' => 10, 'batch' => 'BATCH-001', 'exp' => '+6 months'],
                ['barang_idx' => 1, 'jumlah' => 8, 'batch' => 'BATCH-002', 'exp' => '+8 months'],
            ]],
            ['supplier_idx' => 1, 'tgl' => '-2 months', 'details' => [
                ['barang_idx' => 2, 'jumlah' => 15, 'batch' => 'BATCH-003', 'exp' => '+10 months'],
                ['barang_idx' => 5, 'jumlah' => 5, 'batch' => 'BATCH-004', 'exp' => '+4 months'],
                ['barang_idx' => 6, 'jumlah' => 3, 'batch' => 'BATCH-005', 'exp' => '+5 months'],
            ]],
            ['supplier_idx' => 2, 'tgl' => '-6 weeks', 'details' => [
                ['barang_idx' => 10, 'jumlah' => 12, 'batch' => 'BATCH-006', 'exp' => '+7 months'],
                ['barang_idx' => 11, 'jumlah' => 6, 'batch' => 'BATCH-007', 'exp' => '+9 months'],
                ['barang_idx' => 12, 'jumlah' => 4, 'batch' => 'BATCH-008', 'exp' => '+11 months'],
            ]],
            ['supplier_idx' => 3, 'tgl' => '-1 month', 'details' => [
                ['barang_idx' => 15, 'jumlah' => 20, 'batch' => 'BATCH-009', 'exp' => '+12 months'],
                ['barang_idx' => 16, 'jumlah' => 10, 'batch' => 'BATCH-010', 'exp' => '+6 months'],
            ]],
            ['supplier_idx' => 0, 'tgl' => '-2 weeks', 'details' => [
                ['barang_idx' => 0, 'jumlah' => 5, 'batch' => 'BATCH-011', 'exp' => '+7 months'],
                ['barang_idx' => 3, 'jumlah' => 10, 'batch' => 'BATCH-012', 'exp' => '+9 months'],
            ]],
            ['supplier_idx' => 4, 'tgl' => '-10 days', 'details' => [
                ['barang_idx' => 20, 'jumlah' => 8, 'batch' => 'BATCH-013', 'exp' => '+8 months'],
                ['barang_idx' => 21, 'jumlah' => 6, 'batch' => 'BATCH-014', 'exp' => '+5 months'],
                ['barang_idx' => 22, 'jumlah' => 4, 'batch' => 'BATCH-015', 'exp' => '+6 months'],
            ]],
            ['supplier_idx' => 5, 'tgl' => '-5 days', 'details' => [
                ['barang_idx' => 25, 'jumlah' => 15, 'batch' => 'BATCH-016', 'exp' => '+10 months'],
                ['barang_idx' => 26, 'jumlah' => 12, 'batch' => 'BATCH-017', 'exp' => '+7 months'],
            ]],
        ];

        foreach ($penerimaanItems as $pi) {
            $tgl = now()->modify($pi['tgl']);
            $totalJumlah = collect($pi['details'])->sum('jumlah');
            $barangIds = collect($pi['details'])->pluck('barang_idx')->unique();

            $noTerima = 'TRM-' . $tgl->format('Ymd') . strtoupper(bin2hex(random_bytes(3)));

            $penerimaan = PenerimaanBarang::create([
                'no_terima' => $noTerima,
                'supplier_id' => $suppliers[$pi['supplier_idx']]->id,
                'user_id' => $staff->id,
                'tgl_terima' => $tgl,
                'status_verifikasi' => 'verified',
            ]);

            foreach ($pi['details'] as $di) {
                $barang = $barangs[$di['barang_idx']];
                $detail = DetailPenerimaan::create([
                    'penerimaan_barang_id' => $penerimaan->id,
                    'barang_id' => $barang->id,
                    'jumlah' => $di['jumlah'],
                    'batch_number' => $di['batch'],
                    'tgl_kadaluarsa' => now()->modify($di['exp']),
                ]);

                $barangStok = BarangStok::create([
                    'barang_id' => $barang->id,
                    'detail_penerimaan_id' => $detail->id,
                    'penerimaan_barang_id' => $penerimaan->id,
                    'batch_number' => $di['batch'],
                    'stok' => $di['jumlah'],
                    'tgl_kadaluarsa' => now()->modify($di['exp']),
                    'tgl_masuk' => $tgl,
                    'harga_beli' => $barang->harga_beli,
                ]);

                $stokSebelum = $barang->stok;
                $barang->increment('stok', $di['jumlah']);

                StockMovement::create([
                    'barang_id' => $barang->id,
                    'barang_stok_id' => $barangStok->id,
                    'user_id' => $staff->id,
                    'type' => 'in',
                    'quantity' => $di['jumlah'],
                    'before_quantity' => $stokSebelum,
                    'after_quantity' => $barang->stok,
                    'reason' => "Seeder: Penerimaan #{$noTerima}",
                    'reference_id' => $penerimaan->id,
                    'reference_type' => PenerimaanBarang::class,
                ]);
            }
        }

        $barangKeluarItems = [
            ['tgl' => '-1 month', 'items' => [
                ['barang_idx' => 0, 'jumlah' => 8],
                ['barang_idx' => 5, 'jumlah' => 2],
                ['barang_idx' => 10, 'jumlah' => 5],
            ], 'ket' => 'Pesanan rutin bulanan'],
            ['tgl' => '-2 weeks', 'items' => [
                ['barang_idx' => 0, 'jumlah' => 4],
                ['barang_idx' => 2, 'jumlah' => 6],
            ], 'ket' => 'Supplier A'],
            ['tgl' => '-1 week', 'items' => [
                ['barang_idx' => 15, 'jumlah' => 8],
                ['barang_idx' => 20, 'jumlah' => 3],
            ], 'ket' => 'Pesanan bakery'],
            ['tgl' => '-3 days', 'items' => [
                ['barang_idx' => 25, 'jumlah' => 5],
                ['barang_idx' => 26, 'jumlah' => 4],
                ['barang_idx' => 11, 'jumlah' => 3],
            ], 'ket' => 'Restock harian'],
        ];

        foreach ($barangKeluarItems as $bki) {
            $tgl = now()->modify($bki['tgl']);
            $noKeluar = 'KLR-' . $tgl->format('Ymd') . strtoupper(bin2hex(random_bytes(3)));

            $barangKeluar = BarangKeluar::create([
                'no_keluar' => $noKeluar,
                'user_id' => $staff->id,
                'tgl_keluar' => $tgl,
                'keterangan' => $bki['ket'],
            ]);

            foreach ($bki['items'] as $ii) {
                $barang = $barangs[$ii['barang_idx']];
                $sisaKurang = $ii['jumlah'];

                $batches = BarangStok::where('barang_id', $barang->id)
                    ->where('stok', '>', 0)
                    ->orderBy('tgl_kadaluarsa', 'asc')
                    ->orderBy('tgl_masuk', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($sisaKurang <= 0) break;

                    $ambil = min($batch->stok, $sisaKurang);
                    $stokSebelum = $batch->stok;
                    $batch->decrement('stok', $ambil);
                    $sisaKurang -= $ambil;

                    DetailBarangKeluar::create([
                        'barang_keluar_id' => $barangKeluar->id,
                        'barang_id' => $barang->id,
                        'barang_stok_id' => $batch->id,
                        'jumlah' => $ambil,
                    ]);

                    StockMovement::create([
                        'barang_id' => $barang->id,
                        'barang_stok_id' => $batch->id,
                        'user_id' => $staff->id,
                        'type' => 'out',
                        'quantity' => $ambil,
                        'before_quantity' => $stokSebelum,
                        'after_quantity' => $stokSebelum - $ambil,
                        'reason' => "Seeder: Barang Keluar #{$noKeluar}",
                        'reference_id' => $barangKeluar->id,
                        'reference_type' => BarangKeluar::class,
                    ]);
                }

                $barang->decrement('stok', $ii['jumlah']);
            }
        }

        $this->command->info('Seeded: ' . Kategori::count() . ' categories, ' . Barang::count() . ' items');
        $this->command->info('Seeded: ' . Supplier::count() . ' suppliers');
        $this->command->info('Seeded: ' . PenerimaanBarang::count() . ' penerimaan with ' . DetailPenerimaan::count() . ' details');
        $this->command->info('Seeded: ' . BarangStok::count() . ' batch stocks');
        $this->command->info('Seeded: ' . BarangKeluar::count() . ' barang keluar with ' . DetailBarangKeluar::count() . ' details');
        $this->command->info('Seeded: ' . StockMovement::count() . ' stock movements');
    }
}
