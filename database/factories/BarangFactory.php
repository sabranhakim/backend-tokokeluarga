<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

class BarangFactory extends Factory
{
    protected $model = Barang::class;

    public function definition(): array
    {
        $hargaBeli = $this->faker->numberBetween(5000, 100000);
        $hargaJual = $hargaBeli + $this->faker->numberBetween(1000, 20000);

        static $kodeCounter = null;
        if ($kodeCounter === null) {
            $maxNumber = (int) Barang::withoutGlobalScope('active')
                ->withTrashed()
                ->get('kode_barang')
                ->map(function ($barang) {
                    preg_match('/BRG-(\d+)/', $barang->kode_barang, $matches);
                    return isset($matches[1]) ? (int) $matches[1] : 0;
                })
                ->max();
            $kodeCounter = $maxNumber + 1;
        }

        return [
            'kode_barang' => 'BRG-' . str_pad((string) $kodeCounter, 3, '0', STR_PAD_LEFT),
            'nama_barang' => \Illuminate\Support\Str::limit($this->faker->word().' '.$this->faker->word(), 30, ''),
            'kategori_id' => Kategori::factory(),
            'satuan' => $this->faker->randomElement(['Pcs', 'Box', 'Kg', 'Liter', 'Gram']),
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaJual,
            'stok' => $this->faker->numberBetween(0, 500),
            'stok_minimal' => $this->faker->numberBetween(5, 50),
        ];
    }
}
