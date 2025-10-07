<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\Golongan;
use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengajar;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\Pengajar>
 */
class PengajarFactory extends Factory
{
    protected $model = Pengajar::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pegawai_id' => function () {
                return Pegawai::inRandomOrder()->first()->id;
            },
            'golongan_id' => function () {
                return Golongan::inRandomOrder()->first()->id;
            },
            'lembaga_id' => function () {
                return Lembaga::inRandomOrder()->first()->id;
            },
            'keterangan_jabatan' => $this->faker->randomElement([
                'Guru Senior',
                'Administrasi Keuangan',
                'Kepala Sekolah',
                'Kepala Tata Usaha',
                'Staff Kesiswaan',
                'Kepala Madrasah',
                'Koordinator BK',
                'Kepala Perpustakaan',
                'Guru Mata Pelajaran',
                'Guru BK (Bimbingan Konseling)',
                'Guru Pendamping Khusus (GPK)',
                'Wali Kelas',
                'Ketua Program Keahlian',
                'Pembina OSIS',
                'Pembina Pramuka',
                'Kepala Laboratorium',
                'Kepala Bengkel',
                'Petugas TU (Tata Usaha)',
                'Petugas Perpustakaan',
                'Petugas Koperasi Sekolah',
            ]),
            'jabatan' => $this->faker->randomElement(['kultural', 'tetap', 'kontrak', 'pengkaderan']),
            'tahun_masuk' => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'tahun_akhir' => null,
            'status_aktif' => $this->faker->randomElement(['aktif', 'tidak aktif']),
            'created_by' => 1,
            'updated_by' => null,
        ];
    }
}
