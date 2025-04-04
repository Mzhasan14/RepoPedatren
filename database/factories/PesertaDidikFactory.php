<?php

namespace Database\Factories;

use App\Models\Biodata;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PesertaDidik>
 */
class PesertaDidikFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'id_biodata' => Biodata::inRandomOrder()->first()?->id,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
