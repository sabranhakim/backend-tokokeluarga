<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kategori>
 */
class KategoriFactory extends Factory
{
    protected $model = Kategori::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kategori' => $this->faker->unique()->randomElement([
                'Bahan Kue',
                'Peralatan Bakery',
                'Minuman',
                'Snack',
                'Plastik & Packaging',
                'Bumbu Dapur',
                'Sembako',
                'Susu & Keju',
                'Cokelat & Selai',
                'Tepung & Gandum'
            ]),
        ];
    }
}
