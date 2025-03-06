<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\KategoriGolongan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\KategoriGolongan>
 */
class KategoriGolonganFactory extends Factory
{
    protected $model = KategoriGolongan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kategori_golongan' => $this->faker->randomElement(['Kategori A', 'Kategori B', 'Kategori C']),
            'created_by' => 1,
            'status' => $this->faker->boolean(),
        ];
    }
}
