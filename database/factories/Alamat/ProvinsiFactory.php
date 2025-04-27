<?php

namespace Database\Factories\Alamat;

use App\Models\Alamat\Provinsi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alamat\Provinsi>
 */
class ProvinsiFactory extends Factory
{
    protected $model = Provinsi::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'negara_id' => (new NegaraFactory())->create()->id,
            'nama_provinsi' => $this->faker->state,
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
            'status' => true,
        ];
    }
}
