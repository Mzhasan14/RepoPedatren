<?php

namespace Database\Factories\Pegawai;

use Illuminate\Support\Str;
use App\Models\Pegawai\Karyawan;
use Database\Factories\Pendidikan\LembagaFactory;
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
        $tanggalMulai = $this->faker->dateTimeBetween('-10 years', 'now');
        $tanggalSelesai = $this->faker->boolean(70) // 70% kemungkinan punya tanggal_selesai
            ? $this->faker->dateTimeBetween($tanggalMulai, 'now')
            : null; // NULL jika masih menjabat
        return [
            'pegawai_id' => (new PegawaiFactory())->create()->id,
            'golongan_jabatan_id' => (new GolonganJabatanFactory())->create()->id,
            'lembaga_id' => (new LembagaFactory())->create()->id,
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
            'tanggal_selesai' => $tanggalSelesai,
            'status_aktif' => $this->faker->randomElement(['aktif', 'tidak aktif']),
            'created_by' => 1,
        ];
    }
}
