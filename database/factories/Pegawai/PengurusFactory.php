<?php

namespace Database\Factories\Pegawai;
use Illuminate\Support\Str;
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
        $tanggalSelesai = $this->faker->boolean(70) // 70% kemungkinan punya tanggal_selesai
            ? $this->faker->dateTimeBetween($tanggalMulai, 'now')
            : null; // NULL jika masih menjabat
        return [
            'id' => (string) Str::uuid(),
            'pegawai_id' =>(new PegawaiFactory())->create()->id,
            'golongan_jabatan_id' => (new GolonganJabatanFactory())->create()->id,
            'jabatan' => $this->faker->randomElement(['kultural', 'tetap', 'kontrak', 'pengkaderan']),
            'satuan_kerja' => $this->faker->company,
            'keterangan_jabatan' => $this->faker->randomElement([
                'Pengasuh',
                'Ketua Dewan Pengasuh',
                'Wakil Ketua Pengasuh',
                'Sekretaris Dewan Pengasuh',
                'Anggota Dewan Pengasuh',
                'Guru Pembimbing',
                'Dosen Tamu'
            ]),
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_akhir' => $tanggalSelesai,
            'status_aktif' => $this->faker->randomElement(['aktif', 'tidak aktif']),
            'created_by' => 1, // ID pengguna yang membuat data
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
