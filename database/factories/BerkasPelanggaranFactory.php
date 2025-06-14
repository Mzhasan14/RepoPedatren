<?php

namespace Database\Factories;

use App\Models\Pelanggaran;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BerkasPelanggaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pelanggaran_id' => Pelanggaran::inRandomOrder()->first()->id ?? Pelanggaran::factory(),
            'file_path' => 'storage/berkas/'.Str::random(10).'.png',
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
            'deleted_at' => null,
        ];
    }
}
