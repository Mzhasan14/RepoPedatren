<?php

namespace Database\Factories;

use App\Models\Biodata;
use Illuminate\Support\Str;
use App\Models\PesertaDidik;
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
            'id'          => (string) Str::uuid(),
            // Ini akan memanggil Biodata::factory()->create() secara otomatis
            'id_biodata'  => Biodata::factory(),
            'created_by'  => 1,
            'updated_by'  => null,
            'status'      => true,
        ];
    }
}
