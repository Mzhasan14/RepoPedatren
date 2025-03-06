<?php

namespace Database\Factories;

use App\Models\Pegawai\Golongan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\Golongan>
 */
class GolonganFactory extends Factory
{
    protected $model = Golongan::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_golongan' => $this->faker->randomElement(['Golongan I', 'Golongan II', 'Golongan III']),
            'id_kategori_golongan' => (new KategoriGolonganFactory())->create()->id,
            'created_by' => 1,
            'status' => $this->faker->boolean(),
        ];
    }
}
