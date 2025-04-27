<?php

namespace Database\Factories\Pegawai;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\MateriAjar>
 */
class MateriAjarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pengajar_id' => (new PengajarFactory())->create()->id,
            'nama_materi' => $this->faker->sentence(3),
            'jumlah_menit' => $this->faker->numberBetween(30, 180), // Antara 30 - 180 menit
            'created_by' => 1,
            'status' => $this->faker->boolean(80)
        ];
    }
}
