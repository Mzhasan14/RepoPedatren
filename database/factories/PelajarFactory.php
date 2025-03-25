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
            'tanggal_masuk_pelajar' => $this->faker->date,
            'angkatan_pelajar' => $this->faker->year,
            'tanggal_keluar_pelajar' => null,
            'no_induk' => $this->faker->unique()->numerify('########'),
            'created_by' => 1,
            'updated_by' => null,
            'status_pelajar' => $this->faker->randomElement([
                'aktif'
            ]),
        ];
    }
}
