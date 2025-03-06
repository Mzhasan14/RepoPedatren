<?php

namespace Database\Factories;

use App\Models\Perizinan;
use Database\Factories\Kewaliasuhan\Wali_asuhFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Perizinan>
 */
class PerizinanFactory extends Factory
{
    protected $model = Perizinan::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_peserta_didik' => (new Peserta_didikFactory())->create()->id,
            'id_wali_asuh' => (new Wali_asuhFactory())->create()->id,
            'pembuat' => $this->faker->name,
            'biktren' => $this->faker->word,
            'kamtib' => rand(1, 50),
            'alasan_izin' => $this->faker->sentence,
            'alamat_tujuan' => $this->faker->address,
            'tanggal_mulai' => $this->faker->date,
            'tanggal_akhir' => $this->faker->date,
            'jenis_izin' => $this->faker->randomElement(['Personal', 'Rombongan']),
            'status_izin' => $this->faker->randomElement(['sedang proses izin', 'perizinan diterima', 'sudah berada diluar pondok', 'perizinan ditolak', 'dibatalkan']),
            'status_kembali' => $this->faker->randomElement(['telat', 'telat(sudah kembali)', 'telat(belum kembali)', 'kembali tepat waktu']),
            'keterangan' => $this->faker->sentence,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
