<?php

namespace Database\Factories;

use App\Models\Pelanggaran;
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
            'id_peserta_didik' => (new PesertaDidikFactory())->create()->id,
            'status_pelanggaran' => $this->faker->randomElement(['Belum diproses', 'Sedang diproses', 'Sudah diproses']),
            'jenis_putusan' => $this->faker->randomElement(['Belum ada putusan', 'Disanksi', 'Dibebaskan']),
            'jenis_pelanggaran' => $this->faker->randomElement(['Ringan', 'Sedang', 'Berat']),
            'keterangan' => $this->faker->sentence,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
