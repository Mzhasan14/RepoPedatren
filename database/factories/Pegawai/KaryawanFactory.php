<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\Karyawan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\Karyawan>
 */
class KaryawanFactory extends Factory
{
    protected $model = Karyawan::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pegawai' => (new PegawaiFactory())->create()->id,
            'id_golongan' => (new GolonganFactory())->create()->id,
            'keterangan_jabatan' => $this->faker->randomElement([
                    'Kepala Sekolah',
                    'Wakil Kepala Bag. Kurikulum',
                    'Wakil Kepala Bag. Kesiswaan',
                    'Wakil Kepala Bag. Sarana Prasarana',
                    'Wakil Kepala Bag. Humas dan Kendali Mutu',
                    'Kepala Tata Usaha',
                    'Staff Kesiswaan',
                    'Operator dan Ketatausahaan (Dapodik, Pindatron, Simpatika)',
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
                    'Mudir (Pimpinan Pesantren)',
                    'Ustadz/Ustadzah',
                    'Pembina Asrama',
                    'Kepala Ma had',
                    'Sekretaris Pesantren',
                    'Pengurus Santri',
                    'Bagian Keamanan Pesantren',
                    'Bagian Kesehatan Pesantren',
                    'Bagian Konsumsi dan Dapur Pesantren',
                ]),
            'jabatan' => $this->faker->jobTitle,
            'created_by' => 1,
            'status' => $this->faker->boolean,
        ];
    }
}
