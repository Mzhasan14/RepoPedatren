<?php

namespace Database\Factories\Kewilayahan;

use App\Models\Kewilayahan\Domisili;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kewilayahan\Domisili>
 */
class DomisiliFactory extends Factory
{
    protected $model = Domisili::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_kamar' => (new KamarFactory())->create()->id,
            'nama_domisili' => $this->faker->word,
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
            'status' => true,
        ];
    }
}
