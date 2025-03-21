<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\Pengajar;
use Database\Factories\Pendidikan\LembagaFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\Pengajar>
 */
class PengajarFactory extends Factory
{
    protected $model = Pengajar::class;

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
            'jabatan' => $this->faker->jobTitle,
            'created_by' => 1,
            'updated_by' => null,
            'status' => $this->faker->boolean(),
        ];
    }
}
