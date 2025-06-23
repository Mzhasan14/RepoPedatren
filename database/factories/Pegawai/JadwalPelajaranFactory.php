<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\JadwalPelajaran;
use App\Models\Pegawai\JamPelajaran;
use App\Models\Pegawai\MataPelajaran;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Lembaga;
use App\Models\Pendidikan\Rombel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\JadwalPelajaran>
 */
class JadwalPelajaranFactory extends Factory
{
    protected $model = JadwalPelajaran::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hari' => $this->faker->randomElement(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']),
            'semester_id' => $this->faker->randomElement([1, 2]),
            'kelas_id' => Kelas::inRandomOrder()->value('id') ?? Kelas::factory(),
            'lembaga_id' => Lembaga::inRandomOrder()->value('id') ?? null,
            'jurusan_id' => Jurusan::inRandomOrder()->value('id') ?? null,
            'rombel_id' => Rombel::inRandomOrder()->value('id') ?? null,
            'mata_pelajaran_id' => MataPelajaran::inRandomOrder()->value('id') ?? MataPelajaran::factory(),
            'jam_pelajaran_id' => JamPelajaran::inRandomOrder()->value('id') ?? JamPelajaran::factory(),
            'created_by' => 1,
        ];
    }
}
