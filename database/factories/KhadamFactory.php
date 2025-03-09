<?php

namespace Database\Factories;

use App\Models\Khadam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Khadam>
 */
class KhadamFactory extends Factory
{
    protected $model = Khadam::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_biodata' => (new BiodataFactory())->create()->id,
            'keterangan' => $this->faker->sentence,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
