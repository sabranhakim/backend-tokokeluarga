<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed User
        User::factory()->create([
            'name' => 'Admin Toko',
            'email' => 'admin@tokokeluarga.com',
            'password' => bcrypt('password'),
        ]);

        // Seed Kategori
        $sembako = Kategori::create(['nama_kategori' => 'Sembako']);
        $snack = Kategori::create(['nama_kategori' => 'Snack']);
        $minuman = Kategori::create(['nama_kategori' => 'Minuman']);

        // Seed Supplier
        $supplier1 = Supplier::create([
            'nama_supplier' => 'PT. Sumber Makmur',
            'alamat' => 'Jl. Industri No. 12',
            'no_telp' => '08123456789',
        ]);

        $supplier2 = Supplier::create([
            'nama_supplier' => 'CV. Jaya Bersama',
            'alamat' => 'Jl. Dagang No. 45',
            'no_telp' => '08987654321',
        ]);

        // Seed Barang
        Barang::create([
            'kode_barang' => 'BRG001',
            'nama_barang' => 'Beras 5kg',
            'kategori_id' => $sembako->id,
            'satuan' => 'pcs',
            'harga_beli' => 60000,
            'harga_jual' => 75000,
            'stok' => 50,
        ]);

        Barang::create([
            'kode_barang' => 'BRG002',
            'nama_barang' => 'Minyak Goreng 1L',
            'kategori_id' => $sembako->id,
            'satuan' => 'pcs',
            'harga_beli' => 14000,
            'harga_jual' => 16500,
            'stok' => 100,
        ]);

        Barang::create([
            'kode_barang' => 'BRG003',
            'nama_barang' => 'Chitato 68g',
            'kategori_id' => $snack->id,
            'satuan' => 'pcs',
            'harga_beli' => 8000,
            'harga_jual' => 10500,
            'stok' => 40,
        ]);
    }
}
