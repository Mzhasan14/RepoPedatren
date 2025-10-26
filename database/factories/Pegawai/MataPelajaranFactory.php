<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\MataPelajaran;
use App\Models\Pegawai\Pengajar;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\MataPelajaran>
 */
class MataPelajaranFactory extends Factory
{
    protected $model = MataPelajaran::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // $kode = strtoupper($this->faker->bothify('MP-###'));
        // $mapel = $this->faker->randomElement([
        //     'Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika',
        //     'Kimia', 'Biologi', 'Sejarah', 'Ekonomi', 'Geografi',
        //     'Pendidikan Agama', 'Seni Budaya', 'PJOK'
        // ]);
        return [
            'lembaga_id' => Lembaga::inRandomOrder()->value('id'),
            'kode_mapel' => $this->faker->unique()->bothify('MP-###'),
            'nama_mapel' => $this->faker->word(),
            'pengajar_id' => 1, // tidak dipakai, akan ditentukan di seeder
            'status' => true,
            'created_by' => 1,
        ];
    }
}
