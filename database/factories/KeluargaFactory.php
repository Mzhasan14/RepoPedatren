<?php

namespace Database\Factories;

use App\Models\Biodata;
use App\Models\HubunganKeluarga;
use App\Models\Keluarga;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Keluarga>
 */
class KeluargaFactory extends Factory
{
    protected $model = Keluarga::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_kk' => $this->faker->numerify('###############'),
            'id_biodata' => Biodata::inRandomOrder()->first()->id ?? Biodata::factory(),
            'id_status_keluarga' => HubunganKeluarga::inRandomOrder()->first()->id ?? HubunganKeluarga::factory(),
            'created_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'status' => true,
        ];
    }
}
