<?php

namespace Database\Factories\Pendidikan;

use App\Models\Pendidikan\Jurusan;
use Database\Factories\LembagaFactory;
use Database\Factories\Pendidikan\LembagaFactory as PendidikanLembagaFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pendidikan\Jurusan>
 */
class JurusanFactory extends Factory
{
    protected $model = Jurusan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_jurusan' => $this->faker->word(),
            'lembaga_id' => (new PendidikanLembagaFactory())->create()->id,
            'created_by' => 1,
            'status' => $this->faker->boolean(),
        ];
    }
}
