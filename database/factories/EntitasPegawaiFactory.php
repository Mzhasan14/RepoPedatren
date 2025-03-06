<?php

namespace Database\Factories;

use App\Models\Pegawai\EntitasPegawai;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\EntitasPegawai>
 */
class EntitasPegawaiFactory extends Factory
{
    protected $model = EntitasPegawai::class;
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
            'tanggal_masuk' => $this->faker->date(),
            'tanggal_keluar' => $this->faker->optional()->date(),
            'created_by' => 1,
            'status' => $this->faker->boolean(),
        ];
    }
}
