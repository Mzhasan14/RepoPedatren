<?php

namespace Database\Factories;

use App\Models\Catatan_afektif;
use App\Models\Santri;
use Database\Factories\Kewaliasuhan\Wali_asuhFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Catatan_afektif>
 */
class CatatanAfektifFactory extends Factory
{
    protected $model = Catatan_afektif::class;

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
            'id_santri' => Santri::inRandomOrder()->first()->id ?? Santri::factory(),
            'id_wali_asuh' => (new Wali_asuhFactory)->create()->id,
            'kepedulian_nilai' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'kepedulian_tindak_lanjut' => $this->faker->sentence(),
            'kebersihan_nilai' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'kebersihan_tindak_lanjut' => $this->faker->sentence(),
            'akhlak_nilai' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'akhlak_tindak_lanjut' => $this->faker->sentence(),
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
