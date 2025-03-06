<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\JenisBerkas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\JenisBerkas>
 */
class JenisBerkasFactory extends Factory
{
    protected $model = JenisBerkas::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type_jenis_berkas' => $this->faker->word,
            'nama_jenis_berkas' => $this->faker->sentence(3),
            'created_by' => 1,
            'updated_by' => null,
            'status' => $this->faker->boolean,
        ];
    }
}
