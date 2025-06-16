<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\WaliKelas;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Lembaga;
use App\Models\Pendidikan\Rombel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\Walikelas>
 */
class WaliKelasFactory extends Factory
{
    protected $model = WaliKelas::class;

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
            'lembaga_id' => function () {
                return Lembaga::inRandomOrder()->first()->id;
            },
            'jurusan_id' => function () {
                return Jurusan::inRandomOrder()->first()->id;
            },
            'kelas_id' => function () {
                return Kelas::inRandomOrder()->first()->id;
            },
            'rombel_id' => function () {
                return Rombel::inRandomOrder()->first()->id;
            },
            'jumlah_murid' => $this->faker->numberBetween(20, 40),
            'periode_awal' => $tanggalMulai,
            'periode_akhir' => null,
            'created_by' => 1,
            'updated_by' => null,
            'status_aktif' => $this->faker->randomElement(['aktif', 'tidak aktif']),

        ];
    }
}
