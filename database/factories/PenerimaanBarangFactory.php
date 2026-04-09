<?php

namespace Database\Factories;

use App\Models\PenerimaanBarang;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PenerimaanBarang>
 */
class PenerimaanBarangFactory extends Factory
{
    protected $model = PenerimaanBarang::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_terima' => 'TRM-' . $this->faker->unique()->numberBetween(100000, 999999),
            'supplier_id' => Supplier::factory(),
            'user_id' => User::factory(),
            'tgl_terima' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'foto_bon' => null,
            'status_verifikasi' => $this->faker->randomElement(['pending', 'verified']),
        ];
    }
}
