<?php

namespace Database\Factories\Kewilayahan;

use App\Models\Kewilayahan\Kamar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kewilayahan\Kamar>
 */
class KamarFactory extends Factory
{
    protected $model = Kamar::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'blok_id' => (new BlokFactory())->create()->id,
            'nama_kamar' => $this->faker->word,
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
            'status' => true,
        ];
    }
}
