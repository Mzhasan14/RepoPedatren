<?php

namespace Database\Factories\Kewilayahan;

use App\Models\Kewilayahan\Blok;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kewilayahan\Blok>
 */
class BlokFactory extends Factory
{
    protected $model = Blok::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wilayah_id' => (new WilayahFactory())->create()->id,
            'nama_blok' => $this->faker->word,
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
            'status' => true,
        ];
    }
}
