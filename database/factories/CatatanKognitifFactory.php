<?php

namespace Database\Factories;

use App\Models\Catatan_kognitif;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Santri;
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
        $tanggalMulai = $this->faker->dateTimeBetween('-10 years', 'now');
        $status = $this->faker->boolean();
        $tanggalSelesai = $status ? null : $this->faker->dateTimeBetween($tanggalMulai, 'now');

        // Fungsi helper tindak lanjut
        $generateTindakLanjut = function ($nilai) {
            return match ($nilai) {
                'A' => 'Bagus, harap dipertahankan',
                'B' => 'Cukup baik, tetap ditingkatkan',
                'C' => 'Perlu perhatian dan pembinaan',
                'D' => 'Kurang, perlu bimbingan intensif',
                'E' => 'Buruk, segera ditindaklanjuti secara serius',
                default => 'Perlu evaluasi lanjutan',
            };
        };

        // Nilai acak untuk setiap kategori
        $kebahasaan = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']);
        $kitab = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']);
        $tahfidz = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']);
        $furudul = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']);
        $tulis = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']);
        $baca = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']);

        return [
            'id_santri' => null,
            'id_wali_asuh' => null,
            'kebahasaan_nilai' => $kebahasaan,
            'kebahasaan_tindak_lanjut' => $generateTindakLanjut($kebahasaan),
            'baca_kitab_kuning_nilai' => $kitab,
            'baca_kitab_kuning_tindak_lanjut' => $generateTindakLanjut($kitab),
            'hafalan_tahfidz_nilai' => $tahfidz,
            'hafalan_tahfidz_tindak_lanjut' => $generateTindakLanjut($tahfidz),
            'furudul_ainiyah_nilai' => $furudul,
            'furudul_ainiyah_tindak_lanjut' => $generateTindakLanjut($furudul),
            'tulis_alquran_nilai' => $tulis,
            'tulis_alquran_tindak_lanjut' => $generateTindakLanjut($tulis),
            'baca_alquran_nilai' => $baca,
            'baca_alquran_tindak_lanjut' => $generateTindakLanjut($baca),
            'tanggal_buat' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'created_by' => 1,
            'updated_by' => 1,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
