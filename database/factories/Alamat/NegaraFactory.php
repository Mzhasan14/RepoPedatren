<?php

namespace Database\Factories\Alamat;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alamat\Negara>
 */
class NegaraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_negara' => $this->faker->country,
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
            'status' => true,
        ];
    }
}
