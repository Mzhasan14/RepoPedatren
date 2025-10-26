<?php

namespace Database\Factories;

use App\Models\Biodata;
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
            'biodata_id' => Biodata::inRandomOrder()->first()?->id ?? Biodata::factory(),
            'nis' => $this->faker->unique()->numerify('###########'),
            'tanggal_masuk' => $this->faker->date,
            'tanggal_keluar' => null,
            'created_by' => 1,
            'updated_by' => null,
            'status' => $this->faker->randomElement([
                'aktif',
            ]),
        ];
    }
}
