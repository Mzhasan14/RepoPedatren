<?php

namespace Database\Factories;

use App\Models\Pelanggaran;
use App\Models\Santri;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelanggaran>
 */
class PelanggaranFactory extends Factory
{
    protected $model = Pelanggaran::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Daftar keterangan pelanggaran umum di pesantren
        $pelanggaranRingan = [
            'Terlambat masuk kelas',
            'Tidak memakai peci saat keluar kamar',
            'Berbicara saat jam pelajaran',
            'Tidak hadir apel pagi tanpa izin',
            'Memakai sandal ke masjid',
        ];

        $pelanggaranSedang = [
            'Membawa HP tanpa izin',
            'Bolos pelajaran selama satu hari',
            'Tidur saat pelajaran berlangsung',
            'Meninggalkan pondok tanpa izin resmi',
            'Bertengkar dengan teman sepondok',
        ];

        $pelanggaranBerat = [
            'Melarikan diri dari pondok',
            'Membawa barang terlarang',
            'Merokok di area pondok',
            'Melawan pengurus atau ustadz',
            'Merusak fasilitas pondok dengan sengaja',
        ];

        // Tentukan jenis pelanggaran secara random tapi terhubung dengan keterangan
        $jenisPelanggaran = $this->faker->randomElement(['Ringan', 'Sedang', 'Berat']);
        switch ($jenisPelanggaran) {
            case 'Ringan':
                $keterangan = $this->faker->randomElement($pelanggaranRingan);
                break;
            case 'Sedang':
                $keterangan = $this->faker->randomElement($pelanggaranSedang);
                break;
            case 'Berat':
                $keterangan = $this->faker->randomElement($pelanggaranBerat);
                break;
        }

        // Tentukan status proses dan putusan sesuai jenis pelanggaran
        if ($jenisPelanggaran === 'Ringan') {
            $statusPelanggaran = $this->faker->randomElement(['Belum diproses', 'Sudah diproses']);
            $jenisPutusan = $statusPelanggaran === 'Sudah diproses'
                ? $this->faker->randomElement(['Disanksi', 'Dibebaskan'])
                : 'Belum ada putusan';
            $diprosesMahkamah = false; // Ringan biasanya tidak sampai mahkamah
        } elseif ($jenisPelanggaran === 'Sedang') {
            $statusPelanggaran = $this->faker->randomElement(['Sedang diproses', 'Sudah diproses']);
            $jenisPutusan = $statusPelanggaran === 'Sudah diproses'
                ? $this->faker->randomElement(['Disanksi', 'Dibebaskan'])
                : 'Belum ada putusan';
            $diprosesMahkamah = $this->faker->boolean(30); // Kadang sampai mahkamah
        } else { // Berat
            $statusPelanggaran = $this->faker->randomElement(['Sedang diproses', 'Sudah diproses']);
            $jenisPutusan = $statusPelanggaran === 'Sudah diproses'
                ? $this->faker->randomElement(['Disanksi', 'Dibebaskan'])
                : 'Belum ada putusan';
            $diprosesMahkamah = true; // Berat hampir pasti ke mahkamah
        }

        return [
            'santri_id' => Santri::inRandomOrder()->first()->id ?? Santri::factory(),
            'status_pelanggaran' => $statusPelanggaran,
            'jenis_putusan' => $jenisPutusan,
            'jenis_pelanggaran' => $jenisPelanggaran,
            'diproses_mahkamah' => $diprosesMahkamah,
            'keterangan' => $keterangan,
            'created_by' => 1,
            'updated_by' => null,
        ];
    }
}
