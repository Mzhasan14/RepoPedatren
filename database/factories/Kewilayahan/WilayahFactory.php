<?php

namespace Database\Factories\Kewilayahan;

use App\Models\Kewilayahan\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kewilayahan\Wilayah>
 */
class WilayahFactory extends Factory
{
    protected $model = Wilayah::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_wilayah' => $this->faker->word,
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
            'status' => true,
        ];
    }
}
