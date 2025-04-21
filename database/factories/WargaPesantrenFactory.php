<?php

namespace Database\Factories;

use App\Models\Biodata;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WargaPesantren>
 */
class WargaPesantrenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'biodata_id' => Biodata::inRandomOrder()->first()?->id,
            'niup' =>  $this->faker->unique()->numerify('###########'),
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
