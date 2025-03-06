<?php

namespace Database\Factories\Kewaliasuhan;

use App\Models\Kewaliasuhan\Grup_WaliAsuh;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kewaliasuhan\Grup_WaliAsuh>
 */
class Grup_WaliAsuhhFactory extends Factory
{
    protected $model = Grup_WaliAsuh::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_grup' => $this->faker->word,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
