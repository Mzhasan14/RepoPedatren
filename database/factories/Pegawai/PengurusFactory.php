<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\GolonganJabatan;
use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengurus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\Pengurus>
 */
class PengurusFactory extends Factory
{
    protected $model = Pengurus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggalMulai = $this->faker->dateTimeBetween('-10 years', 'now');

        return [
            'pegawai_id' => function () {
                return Pegawai::inRandomOrder()->first()->id;
            },
            'golongan_jabatan_id' => function () {
                return GolonganJabatan::inRandomOrder()->first()->id;
            },
            'jabatan' => $this->faker->randomElement(['kultural', 'tetap', 'kontrak', 'pengkaderan']),
            'satuan_kerja' => $this->faker->randomElement([
                'Madrasah Tsanawiyah Al-Hikmah',
                'Madrasah Aliyah Negeri 1 Jakarta',
                'Pesantren Darussalam',
                'SMA Islam Terpadu Al-Furqan',
                'SD Negeri 01 Menteng',
                'Kementerian Agama Kabupaten Bandung',
                'Dinas Pendidikan Kota Surabaya',
                'Universitas Islam Negeri Maulana Malik Ibrahim',
                'Pondok Pesantren Nurul Huda',
                'Yayasan Pendidikan Islam Al-Azhar',
                'Balai Diklat Keagamaan Semarang',
                'Kantor Kementerian Agama Provinsi Jawa Timur',
                'MTs Negeri 3 Sleman',
            ]),
            'keterangan_jabatan' => $this->faker->randomElement([
                'Pengasuh',
                'Ketua Dewan Pengasuh',
                'Wakil Ketua Pengasuh',
                'Sekretaris Dewan Pengasuh',
                'Anggota Dewan Pengasuh',
                'Guru Pembimbing',
                'Dosen Tamu',
            ]),
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_akhir' => null,
            'status_aktif' => $this->faker->randomElement(['aktif', 'tidak aktif']),
            'created_by' => 1, // ID pengguna yang membuat data
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
