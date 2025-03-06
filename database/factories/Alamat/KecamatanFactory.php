<?php

namespace Database\Factories\Alamat;

use App\Models\Alamat\Kecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alamat\Kecamatan>
 */
class KecamatanFactory extends Factory
{
    protected $model = Kecamatan::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_kabupaten' => (new KabupatenFactory())->create()->id,
            'nama_kecamatan' => $this->faker->citySuffix,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
