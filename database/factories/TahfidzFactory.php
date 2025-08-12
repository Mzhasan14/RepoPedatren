<?php

namespace Database\Factories;

use App\Models\Santri;
use App\Models\Tahfidz;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tahfidz>
 */
class TahfidzFactory extends Factory
{
    protected $model = Tahfidz::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $suratList = [
            'Al-Fatihah', 'Al-Baqarah', 'Ali Imran', 'An-Nisa', 'Al-Maidah',
            'Al-Anam', 'Al-Araf', 'Al-Anfal', 'At-Taubah', 'Yunus',
            'Hud', 'Yusuf', 'Ar-Rad', 'Ibrahim', 'Al-Hijr'
        ];

        $ayatMulai = $this->faker->numberBetween(1, 100);
        $ayatSelesai = $ayatMulai + $this->faker->numberBetween(1, 5);

        return [
            'santri_id'       => Santri::where('status', 'aktif')->inRandomOrder()->value('id'),
            'tahun_ajaran_id' => TahunAjaran::inRandomOrder()->value('id'),
            'tanggal'         => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'jenis_setoran'   => $this->faker->randomElement(['baru', 'murojaah']),
            'surat'           => $this->faker->randomElement($suratList),
            'ayat_mulai'      => $ayatMulai,
            'ayat_selesai'    => $ayatSelesai,
            'nilai'           => $this->faker->randomElement(['lancar', 'cukup', 'kurang']),
            'catatan'         => $this->faker->optional()->sentence(),
            'status'          => $this->faker->randomElement(['proses', 'tuntas']),
            'created_by'      => 1,
        ];
    }
}
