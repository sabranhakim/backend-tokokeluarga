<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\DetailPenerimaan;
use App\Models\PenerimaanBarang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetailPenerimaan>
 */
class DetailPenerimaanFactory extends Factory
{
    protected $model = DetailPenerimaan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'penerimaan_barang_id' => PenerimaanBarang::factory(),
            'barang_id' => Barang::factory(),
            'jumlah' => $this->faker->numberBetween(1, 100),
        ];
    }
}
