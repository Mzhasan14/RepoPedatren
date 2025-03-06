<?php

namespace Database\Factories\Pegawai;

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
        return [
            'id_pegawai' =>(new PegawaiFactory())->create()->id,
            'id_golongan' => (new GolonganFactory())->create()->id,
            'satuan_kerja' => $this->faker->company,
            'jabatan' => $this->faker->jobTitle,
            'created_by' => 1,
            'status' => $this->faker->boolean,
        ];
    }
}
