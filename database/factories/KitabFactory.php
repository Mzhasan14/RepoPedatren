<?php

namespace Database\Factories;

use App\Models\Kitab;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kitab>
 */
class KitabFactory extends Factory
{
    protected $model = Kitab::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'nama_kitab' => $this->faker->randomElement([
                'Nadzom Aqidatul Awam',
                'Nadzom Imrithi',
                'Nadzom Alfiyah Ibnu Malik',
                'Nadzom Bina',
                'Nadzom Tashrif',
                'Nadzom Tuhfatul Athfal',
                'Nadzom Jazariyah'
            ]),
            'total_bait' => $this->faker->numberBetween(20, 1000),
            'created_by' => 1, // default admin
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }
}
