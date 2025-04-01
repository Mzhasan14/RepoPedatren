<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\AnakPegawai;
use Database\Factories\PesertaDidikFactory;
use Database\Factories\Peserta_didikFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnakPegawai>
 */
class AnakPegawaiFactory extends Factory
{
    protected $model = AnakPegawai::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_peserta_didik' => (new PesertaDidikFactory())->create()->id,
            'id_pegawai' => (new PegawaiFactory())->create()->id,
            'created_by' => 1,
            'status' => $this->faker->boolean(),
        ];
    }
}
