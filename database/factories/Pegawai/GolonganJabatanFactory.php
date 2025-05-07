<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\GolonganJabatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\GolonganJabatan>
 */
class GolonganJabatanFactory extends Factory
{
    protected $model = GolonganJabatan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_golongan_jabatan' => $this->faker->jobTitle(), // Contoh nama jabatan
            'created_by' => 1, // Bisa kamu ubah sesuai user login/pengguna awal
            'updated_by' => null,
            'status' => $this->faker->boolean(90), // 90% aktif
        ];
    }
}
