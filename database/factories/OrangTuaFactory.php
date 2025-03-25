<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Biodata;
use App\Models\OrangTua;
use App\Models\OrangTuaWali;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrangTua>
 */
class OrangTuaFactory extends Factory
{
    protected $model = OrangTuaWali::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_biodata' => Biodata::inRandomOrder()->first()->id ?? Biodata::factory(),
            'pekerjaan' => $this->faker->jobTitle(),
            'penghasilan' => $this->faker->randomElement([null, '500000', '1000000', '2000000']),
            'wafat' => $this->faker->boolean(),
            'created_by' => User::inRandomOrder()->first()->id ?? User::factory(),
        ];
    }
}
