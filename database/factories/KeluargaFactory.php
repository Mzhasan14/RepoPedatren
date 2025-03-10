<?php

namespace Database\Factories;

use App\Models\Biodata;
use App\Models\Keluarga;
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
            'no_kk' => Biodata::inRandomOrder()->value('no_kk') ?? '1234567890123456',
            'status_wali' => $this->faker->boolean,
            'id_status_keluarga' => (new Status_keluargaFactory())->create()->id,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
