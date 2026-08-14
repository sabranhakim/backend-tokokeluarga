<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            'view dashboard',
            // Stok & master data (read-only)
            'view barang',
            'view kategori',
            'view supplier',
            // Transaksi (read-only)
            'view penerimaan',
            'view barang_keluar',
            'view retur_pembelian',
            'view retur_penjualan',
            'view stock_opname',
            // Laporan & aktivitas
            'manage laporan',
            'manage activity',
        ];

        // Ensure owner role exists with read-only permissions
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $ownerRole->syncPermissions($permissions);

        // Create owner user
        $owner = User::firstOrCreate(
            ['email' => 'owner@tokokeluarga.com'],
            [
                'name' => 'Pemilik Toko',
                'password' => Hash::make('owner123'),
                'is_active' => true,
            ]
        );
        $owner->syncRoles([$ownerRole]);
    }
}
