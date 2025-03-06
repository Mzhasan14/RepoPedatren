<?php

namespace Database\Factories\Pendidikan;

use App\Models\Pendidikan\Rombel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pendidikan\Rombel>
 */
class RombelFactory extends Factory
{
    protected $model = Rombel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_rombel' => $this->faker->word(),
            'id_kelas' => (new KelasFactory())->create()->id,
            'created_by' => 1,
            'status' => $this->faker->boolean(),
        ];
    }
}
