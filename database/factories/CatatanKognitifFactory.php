<?php

namespace Database\Factories;

use App\Models\Catatan_kognitif;
use Database\Factories\Kewaliasuhan\Wali_asuhFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Catatan_kognitif>
 */
class CatatanKognitifFactory extends Factory
{
    protected $model = Catatan_kognitif::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_santri' => (new SantriFactory())->create()->id,
            'id_wali_asuh' => (new Wali_asuhFactory())->create()->id,
            'kebahasaan_nilai' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'kebahasaan_tindak_lanjut' => $this->faker->sentence(),
            'baca_kitab_kuning_nilai' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'baca_kitab_kuning_tindak_lanjut' => $this->faker->sentence(),
            'hafalan_tahfidz_nilai' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'hafalan_tahfidz_tindak_lanjut' => $this->faker->sentence(),
            'furudul_ainiyah_nilai' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'furudul_ainiyah_tindak_lanjut' => $this->faker->sentence(),
            'tulis_alquran_nilai' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'tulis_alquran_tindak_lanjut' => $this->faker->sentence(),
            'baca_alquran_nilai' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'baca_alquran_tindak_lanjut' => $this->faker->sentence(),
            'created_by' => 1,
            'updated_by' => 1,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
