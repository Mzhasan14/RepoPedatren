<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\EntitasPegawai;
use Database\Factories\GolonganFactory;
use Database\Factories\Pegawai\GolonganFactory as PegawaiGolonganFactory;
use Database\Factories\Pegawai\PegawaiFactory as PegawaiPegawaiFactory;
use Database\Factories\PegawaiFactory;
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
            'id_pegawai' => (new PegawaiPegawaiFactory())->create()->id,
            'id_golongan' => (new PegawaiGolonganFactory())->create()->id,
            'tanggal_masuk' => $this->faker->date(),
            'tanggal_keluar' => $this->faker->optional()->date(),
            'created_by' => 1,
            'status' => $this->faker->boolean(),
        ];
    }
}
