<?php

namespace Database\Factories\Alamat;

use App\Models\Alamat\Kabupaten;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alamat\Kabupaten>
 */
class KabupatenFactory extends Factory
{
    protected $model = Kabupaten::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provinsi_id' => (new ProvinsiFactory)->create()->id,
            'nama_kabupaten' => $this->faker->city,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
