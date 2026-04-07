<?php

namespace Database\Seeders;

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
            'manage barang',
            'manage supplier',
            'manage kategori',
            'manage penerimaan',
            'verify penerimaan',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Admin Role and Assign All Permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Create Staff Role with Limited Permissions
        $staffRole = Role::create(['name' => 'staff']);
        $staffRole->givePermissionTo([
            'view dashboard',
            'manage barang',
            'manage penerimaan',
        ]);

        // Create Admin User
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@tokokeluarga.com',
            'password' => Hash::make('password123'),
        ]);
        $admin->assignRole($adminRole);

        // Create Staff User
        $staff = User::create([
            'name' => 'Staff Gudang',
            'email' => 'staff@tokokeluarga.com',
            'password' => Hash::make('password123'),
        ]);
        $staff->assignRole($staffRole);
    }
}
