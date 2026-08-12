<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_supplier' => \Illuminate\Support\Str::limit($this->faker->company(), 30, ''),
            'alamat' => $this->faker->address(),
            'no_telp' => '+62' . $this->faker->numerify('#########'),
        ];
    }
}
