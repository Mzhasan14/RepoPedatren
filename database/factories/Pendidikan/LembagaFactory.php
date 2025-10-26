<?php

namespace Database\Factories\Pendidikan;

use App\Models\Pendidikan\Lembaga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pendidikan\Lembaga>
 */
class LembagaFactory extends Factory
{
    protected $model = Lembaga::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_lembaga' => $this->faker->company(),
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
            'status' => $this->faker->boolean(),
        ];
    }
}
