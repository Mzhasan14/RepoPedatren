<?php

namespace Database\Factories;

use App\Models\Peserta_didik;
use Database\Factories\Kewilayahan\BlokFactory;
use Database\Factories\Kewilayahan\DomisiliFactory;
use Database\Factories\Kewilayahan\KamarFactory;
use Database\Factories\Kewilayahan\WilayahFactory;
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
            'id_peserta_didik' => Peserta_Didik::whereDoesntHave('santriAktif')->inRandomOrder()->first()?->id ?? Peserta_Didik::factory(),
            'nis' => $this->faker->unique()->numerify('###########'),
            'angkatan_santri' => $this->faker->year,
            'tanggal_masuk_santri' => $this->faker->date,
            'tanggal_keluar_santri' => null,
            'created_by' => 1,
            'updated_by' => null,
            'status_santri' => $this->faker->randomElement([
                'aktif'
            ]),
        ];
    }
}
