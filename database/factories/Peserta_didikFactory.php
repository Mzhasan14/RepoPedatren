<?php

namespace Database\Factories;

use App\Models\Biodata;
use Database\Factories\Kewilayahan\DomisiliFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Peserta_didik>
 */
class Peserta_didikFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_biodata' => Biodata::inRandomOrder()->first()?->id,
            'id_domisili' => (new DomisiliFactory())->create()->id,
            'nis' => $this->faker->unique()->numerify('###########'),
            'anak_keberapa' => rand(1, 5),
            'dari_saudara' => rand(1, 5),
            'tinggal_bersama' => $this->faker->word,
            'tahun_masuk' => $this->faker->date,
            'tahun_keluar' => null,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
