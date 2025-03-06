<?php

namespace Database\Factories;

use App\Models\Biodata;
use App\Models\OrangTua;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrangTua>
 */
class OrangTuaFactory extends Factory
{
    protected $model = OrangTua::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_biodata' => Biodata::inRandomOrder()->first()?->id,
            'pekerjaan' => $this->faker->jobTitle,
            'penghasialan' => rand(1000000, 10000000),
            'wafat' => $this->faker->boolean,
            'status' => true,
        ];
    }
}
