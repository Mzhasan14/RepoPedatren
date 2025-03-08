<?php

namespace Database\Factories\Pegawai;

use App\Models\Biodata;
use App\Models\Pegawai\Pegawai;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\Pegawai>
 */
class PegawaiFactory extends Factory
{
    protected $model = Pegawai::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_biodata' => Biodata::inRandomOrder()->first()?->id,
            'warga_pesantren' => $this->faker->boolean,
            'created_by' => 1,
            'updated_by' => null,
            'status' => $this->faker->boolean(),
        ];
    }
}
