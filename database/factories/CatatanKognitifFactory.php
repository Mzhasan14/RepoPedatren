<?php

namespace Database\Factories;

use App\Models\Santri;
use App\Models\Catatan_kognitif;
use Illuminate\Database\Eloquent\Factories\Factory;
use Database\Factories\Kewaliasuhan\Wali_asuhFactory;

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
        $tanggalMulai = $this->faker->dateTimeBetween('-10 years', 'now');
        $tanggalSelesai = $this->faker->boolean(70) // 70% kemungkinan punya tanggal_selesai
            ? $this->faker->dateTimeBetween($tanggalMulai, 'now')
            : null; // NULL jika masih menjabat
        return [
            'id_santri' =>  Santri::inRandomOrder()->first()->id ?? Santri::factory(),
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
                        'tanggal_buat' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'created_by' => 1,
            'updated_by' => 1,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
