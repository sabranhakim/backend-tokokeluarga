<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\BarangStok;
use App\Models\PenerimaanBarang;
use Illuminate\Database\Eloquent\Factories\Factory;

class BarangStokFactory extends Factory
{
    protected $model = BarangStok::class;

    public function definition(): array
    {
        return [
            'barang_id' => Barang::factory(),
            'penerimaan_barang_id' => PenerimaanBarang::factory(),
            'detail_penerimaan_id' => null,
            'batch_number' => $this->faker->optional(0.7)->bothify('BATCH-####'),
            'stok' => $this->faker->numberBetween(1, 100),
            'tgl_kadaluarsa' => $this->faker->optional(0.8)->dateTimeBetween('+1 month', '+2 years'),
            'tgl_masuk' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'harga_beli' => $this->faker->numberBetween(5000, 100000),
        ];
    }
}
