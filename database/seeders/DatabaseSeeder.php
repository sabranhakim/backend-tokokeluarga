<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin Role
        $adminRole = Role::create(['name' => 'admin']);

        // Create Admin User
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@tokokeluarga.com',
            'password' => Hash::make('password123'),
        ]);

        $admin->assignRole($adminRole);
    }
}
