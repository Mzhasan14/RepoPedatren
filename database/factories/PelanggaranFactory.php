<?php

namespace Database\Factories;

use App\Models\Pelanggaran;
use App\Models\Santri;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelanggaran>
 */
class PelanggaranFactory extends Factory
{
    protected $model = Pelanggaran::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'santri_id' => Santri::inRandomOrder()->first()->id ?? Santri::factory(),
            'status_pelanggaran' => $this->faker->randomElement(['Belum diproses', 'Sedang diproses', 'Sudah diproses']),
            'jenis_putusan' => $this->faker->randomElement(['Belum ada putusan', 'Disanksi', 'Dibebaskan']),
            'jenis_pelanggaran' => $this->faker->randomElement(['Ringan', 'Sedang', 'Berat']),
            'diproses_mahkamah' => $this->faker->boolean,
            'keterangan' => $this->faker->sentence,
            'created_by' => 1,
            'updated_by' => null,
        ];
    }
}
