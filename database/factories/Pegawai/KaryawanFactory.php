<?php

namespace Database\Factories\Pegawai;

use Illuminate\Support\Str;
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
            'id' => (string) Str::uuid(),
            'id_pegawai' => (new PegawaiFactory())->create()->id,
            'id_golongan' => (new GolonganFactory())->create()->id,
            'jabatan' => $this->faker->jobTitle,
            'created_by' => 1,
            'status' => $this->faker->boolean,
        ];
    }
}
