<?php

namespace Database\Factories\Alamat;

use App\Models\Alamat\Desa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alamat\Desa>
 */
class DesaFactory extends Factory
{
    protected $model = Desa::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_kecamatan' => (new KecamatanFactory())->create()->id,
            'nama_desa' => $this->faker->streetName,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
