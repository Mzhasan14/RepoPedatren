<?php

namespace Database\Factories\Pendidikan;

use App\Models\Pendidikan\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pendidikan\Kelas>
 */
class KelasFactory extends Factory
{
    protected $model = Kelas::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kelas' => $this->faker->word(),
            'jurusan_id' => (new JurusanFactory())->create()->id,
            'created_by' => 1,
            'status' => $this->faker->boolean(),
        ];
    }
}
