<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\DetailPenerimaan;
use App\Models\Kategori;
use App\Models\PenerimaanBarang;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenerimaanBarangLargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure we have enough supporting data
        if (Supplier::count() < 50) {
            Supplier::factory(50 - Supplier::count())->create();
        }

        if (Barang::count() < 200) {
            $kategoris = Kategori::all();
            if ($kategoris->isEmpty()) {
                $kategoris = Kategori::factory(10)->create();
            }
            Barang::factory(200 - Barang::count())->recycle($kategoris)->create();
        }

        $suppliers = Supplier::all();
        $barangs = Barang::all();
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('Please run DatabaseSeeder first to create users.');
            return;
        }

        $this->command->info('Starting to seed 1,000 Penerimaan Barang records...');

        // 2. Generate 1,000 PenerimaanBarang records in chunks for performance
        $total = 1000;
        $batchSize = 100;

        for ($i = 0; $i < $total / $batchSize; $i++) {
            PenerimaanBarang::factory($batchSize)
                ->sequence(fn ($sequence) => [
                    'supplier_id' => $suppliers->random()->id,
                    'user_id' => $users->random()->id,
                ])
                ->has(
                    DetailPenerimaan::factory()
                        ->count(rand(1, 5))
                        ->state(function (array $attributes, PenerimaanBarang $penerimaan) use ($barangs) {
                            return ['barang_id' => $barangs->random()->id];
                        }),
                    'detailPenerimaans'
                )
                ->create();
            
            $this->command->info("Seeded " . (($i + 1) * $batchSize) . " records...");
        }

        $this->command->info('Successfully seeded 1,000 Penerimaan Barang records.');
    }
}
