<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\Pengajar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\MateriAjar>
 */
class MateriAjarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pengajar_id' => function () {
                return Pengajar::inRandomOrder()->first()->id;
            },
            'nama_materi' => $this->faker->randomElement([
                // Pelajaran Umum
                'Matematika',
                'Bahasa Indonesia',
                'Bahasa Inggris',
                'IPA',
                'IPS',
                'Pendidikan Pancasila',
                'Ilmu Pengetahuan Alam',
                'Ilmu Pengetahuan Sosial',
                'Teknologi Informasi',
                'Penjaskes',
                'Seni Budaya',

                // Pelajaran Keagamaan / Pesantren
                'Tauhid',
                'Aqidah Akhlak',
                'Fiqih',
                'Al-Qur\'an Hadits',
                'Bahasa Arab',
                'Nahwu',
                'Shorof',
                'Tafsir',
                'Ilmu Tajwid',
                'Sejarah Kebudayaan Islam',
            ]),
            'jumlah_menit' => $this->faker->numberBetween(30, 180), // Antara 30 - 180 menit
            'created_by' => 1,
            'tahun_masuk' => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'tahun_akhir' => $this->faker->boolean(70)
                ? $this->faker->dateTimeBetween($this->faker->dateTimeBetween('-10 years', 'now'), 'now')->format('Y-m-d')
                : null,
            'status_aktif' => $this->faker->randomElement(['aktif', 'tidak aktif']),
        ];
    }
}
