<?php

namespace Database\Factories;

use App\Models\Berkas;
use App\Models\JenisBerkas;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Berkas>
 */
class BerkasFactory extends Factory
{
    protected $model = Berkas::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'biodata_id' => \App\Models\Biodata::factory()->create()->id,
            'jenis_berkas_id' => JenisBerkas::factory(),
            'file_path' => 'storage/berkas/'.Str::random(10).'.png',
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
            'status' => 1,
        ];
    }
}
