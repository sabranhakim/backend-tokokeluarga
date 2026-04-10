<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\DetailPenerimaan;
use App\Models\Kategori;
use App\Models\PenerimaanBarang;
use App\Models\Supplier;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Define Permissions
        $permissions = [
            'view dashboard',
            'manage users',
            'manage roles',
            'view barang',
            'manage barang',
            'view supplier',
            'manage supplier',
            'view kategori',
            'manage kategori',
            'view penerimaan',
            'create penerimaan',
            'verify penerimaan',
            'delete penerimaan',
            'view trash',
            'manage trash',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Admin Role and Assign All Permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Create Staff Role with Limited Permissions
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->givePermissionTo([
            'view dashboard',
            'manage barang',
            'manage penerimaan',
        ]);

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@tokokeluarga.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
            ]
        );
        $admin->assignRole($adminRole);

        // Create Staff User
        $staff = User::firstOrCreate(
            ['email' => 'staff@tokokeluarga.com'],
            [
                'name' => 'Staff Gudang',
                'password' => Hash::make('password123'),
            ]
        );
        $staff->assignRole($staffRole);

        // Dummy Data
        $kategoris = Kategori::factory(10)->create();
        $suppliers = Supplier::factory(10)->create();

        $barangs = Barang::factory(50)->recycle($kategoris)->create();

        PenerimaanBarang::factory(20)
            ->recycle($suppliers)
            ->recycle($staff)
            ->has(
                DetailPenerimaan::factory()
                    ->count(3)
                    ->state(function (array $attributes, PenerimaanBarang $penerimaan) use ($barangs) {
                        return ['barang_id' => $barangs->random()->id];
                    }),
                'detailPenerimaans'
            )
            ->create();

        $this->call(TrashPermissionSeeder::class);
    }
}
