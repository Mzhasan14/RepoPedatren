<?php

namespace Database\Factories\Pegawai;

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
            'id_pegawai' => (new PegawaiFactory())->create()->id,
            'id_golongan' => (new GolonganFactory())->create()->id,
            'keterangan' => $this->faker->paragraph,
            'created_by' => 1,
            'status' => $this->faker->boolean,
        ];
    }
}
