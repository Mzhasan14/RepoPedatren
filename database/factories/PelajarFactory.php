<?php

namespace Database\Factories;

use App\Models\Peserta_didik;
use Database\Factories\Pendidikan\JurusanFactory;
use Database\Factories\Pendidikan\KelasFactory;
use Database\Factories\Pendidikan\LembagaFactory;
use Database\Factories\Pendidikan\RombelFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelajar>
 */
class PelajarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_peserta_didik' => Peserta_Didik::whereDoesntHave('pelajarAktif')->inRandomOrder()->first()?->id ?? Peserta_Didik::factory(),
            'id_lembaga' =>  (new LembagaFactory())->create()->id,
            'id_jurusan' =>  (new JurusanFactory())->create()->id,
            'id_kelas' =>  (new KelasFactory())->create()->id,
            'id_rombel' =>  (new RombelFactory())->create()->id,
            'tanggal_masuk' => $this->faker->date,
            'angkatan' => $this->faker->year,
            'tanggal_keluar' => null,
            'no_induk' => $this->faker->unique()->numerify('########'),
            'created_by' => 1,
            'updated_by' => null,
            'status' => $this->faker->randomElement([
                'aktif', 'tidak aktif'
            ]),
        ];
    }
}
