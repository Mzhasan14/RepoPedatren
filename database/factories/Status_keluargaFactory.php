<?php

namespace Database\Factories;

use App\Models\Status_keluarga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Status_keluarga>
 */
class Status_keluargaFactory extends Factory
{
    protected $model = Status_keluarga::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_status' => $this->faker->word,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
