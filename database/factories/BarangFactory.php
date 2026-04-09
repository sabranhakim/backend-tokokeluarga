<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Barang>
 */
class BarangFactory extends Factory
{
    protected $model = Barang::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hargaBeli = $this->faker->numberBetween(5000, 100000);
        $hargaJual = $hargaBeli + $this->faker->numberBetween(1000, 20000);

        return [
            'kode_barang' => 'BRG-' . $this->faker->unique()->numberBetween(1000, 9999),
            'nama_barang' => $this->faker->word() . ' ' . $this->faker->word(),
            'kategori_id' => Kategori::factory(),
            'satuan' => $this->faker->randomElement(['Pcs', 'Box', 'Kg', 'Liter', 'Gram']),
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaJual,
            'stok' => $this->faker->numberBetween(0, 500),
            'stok_minimal' => $this->faker->numberBetween(5, 50),
        ];
    }
}
