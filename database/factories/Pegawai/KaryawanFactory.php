<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\GolonganJabatan;
use App\Models\Pegawai\Karyawan;
use App\Models\Pegawai\Pegawai;
use App\Models\Pendidikan\Lembaga;
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
        $tanggalMulai = $this->faker->dateTimeBetween('-10 years', 'yesterday');
        return [
            'pegawai_id' => function () {
                return Pegawai::inRandomOrder()->first()->id;
            },
            'golongan_jabatan_id' => function () {
                return GolonganJabatan::inRandomOrder()->first()->id;
            },
            'lembaga_id' => function () {
                return Lembaga::inRandomOrder()->first()->id;
            },
            'jabatan' => $this->faker->randomElement(['kultural', 'tetap', 'kontrak', 'pengkaderan']),
            'keterangan_jabatan' => $this->faker->randomElement([
                'Kepala Tata Usaha',
                'Wakil Kepala Bag. Kurikulum',
                'Guru Senior',
                'Administrasi Keuangan',
                'Pembina Asrama',
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
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => null,
            'status_aktif' => $this->faker->randomElement(['aktif', 'tidak aktif']),
            'created_by' => 1,
        ];
    }
}
