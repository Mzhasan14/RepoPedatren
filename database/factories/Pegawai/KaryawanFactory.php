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
        return [
            'id' => (string) Str::uuid(),
            'pegawai_id' => (new PegawaiFactory())->create()->id,
            'golongan_id' => (new GolonganFactory())->create()->id,
            'lembaga_id' => (new LembagaFactory())->create()->id,
            'jabatan' => $this->faker->jobTitle,
            'status_aktif' => $this->faker->randomElement(['aktif', 'tidak aktif']),
            'created_by' => 1,
            'status' => $this->faker->boolean,
        ];
    }
}
