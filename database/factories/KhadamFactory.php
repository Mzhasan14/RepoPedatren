<?php

namespace Database\Factories;

use App\Models\Khadam;
use Illuminate\Support\Str;
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
            'id' => (string) Str::uuid(),
            'id_biodata' => (new BiodataFactory())->create()->id,
            'keterangan' => $this->faker->sentence,
            'tanggal_mulai' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'tanggal_akhir' => $this->faker->dateTimeBetween('now', '+1 year'),
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
