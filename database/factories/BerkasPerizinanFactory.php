<?php

namespace Database\Factories;

use App\Models\Perizinan;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BerkasPerizinanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'perizinan_id' => Perizinan::inRandomOrder()->first()->id ?? Perizinan::factory(),
            'file_path' => 'storage/berkas/' . Str::random(10) . '.png',
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
            'deleted_at' => null,
        ];
    }
}
