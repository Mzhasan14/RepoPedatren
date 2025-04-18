<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Models\PesertaDidik;
use App\Models\Peserta_didik;
use Database\Factories\Kewilayahan\BlokFactory;
use Database\Factories\Kewilayahan\KamarFactory;
use Database\Factories\Kewilayahan\WilayahFactory;
use Database\Factories\Kewilayahan\DomisiliFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Santri>
 */
class SantriFactory extends Factory
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
            'id_peserta_didik' => PesertaDidik::whereDoesntHave('santriAktif')->inRandomOrder()->first()?->id ?? PesertaDidik::factory(),
            'nis' => $this->faker->unique()->numerify('###########'),
            'tanggal_masuk' => $this->faker->date,
            'tanggal_keluar' => null,
            'created_by' => 1,
            'updated_by' => null,
            'status' => $this->faker->randomElement([
                'aktif'
            ]),
        ];
    }
}
