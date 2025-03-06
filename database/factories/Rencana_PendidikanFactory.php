<?php

namespace Database\Factories;

use App\Models\Rencana_pendidikan;
use Database\Factories\Pendidikan\JurusanFactory;
use Database\Factories\Pendidikan\KelasFactory;
use Database\Factories\Pendidikan\LembagaFactory;
use Database\Factories\Pendidikan\RombelFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rencana_pendidikan>
 */
class Rencana_PendidikanFactory extends Factory
{
    protected $model = Rencana_pendidikan::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_peserta_didik' =>  (new Peserta_didikFactory())->create()->id,
            'id_lembaga' =>  (new LembagaFactory())->create()->id,
            'id_jurusan' =>  (new JurusanFactory())->create()->id,
            'id_kelas' =>  (new KelasFactory())->create()->id,
            'id_rombel' =>  (new RombelFactory())->create()->id,
            'jenis_pendaftaran' => $this->faker->randomElement(['baru', 'mutasi']),
            'mondok' => $this->faker->boolean,
            'alumni' => $this->faker->boolean,
            'no_induk' => $this->faker->unique()->numerify('########'),
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }
}
