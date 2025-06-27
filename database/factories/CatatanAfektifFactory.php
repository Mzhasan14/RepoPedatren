<?php

namespace Database\Factories;

use App\Models\Catatan_afektif;
use App\Models\Kewaliasuhan\Wali_asuh;
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
        $status = $this->faker->boolean();
        $tanggalSelesai = $status ? null : $this->faker->dateTimeBetween($tanggalMulai, 'now');

        // Nilai kategori
        $akhlak_nilai = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']);
        $kepedulian_nilai = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']);
        $kebersihan_nilai = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']);

        // Fungsi helper untuk tindak lanjut berdasarkan nilai
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

        return [
            'id_santri' => null,
            'id_wali_asuh' => null,
            'kepedulian_nilai' => $kepedulian_nilai,
            'kepedulian_tindak_lanjut' => $generateTindakLanjut($kepedulian_nilai),
            'kebersihan_nilai' => $kebersihan_nilai,
            'kebersihan_tindak_lanjut' => $generateTindakLanjut($kebersihan_nilai),
            'akhlak_nilai' => $akhlak_nilai,
            'akhlak_tindak_lanjut' => $generateTindakLanjut($akhlak_nilai),
            'tanggal_buat' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'created_by' => 1,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
