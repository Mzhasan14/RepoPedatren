<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Models\PesertaDidik;
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
            'id' => (string) Str::uuid(),
            'id_peserta_didik' => PesertaDidik::whereDoesntHave('pelajarAktif')->inRandomOrder()->first()?->id ?? PesertaDidik::factory(),
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
