<?php

namespace Database\Factories;

use App\Models\Santri;
use App\Models\Catatan_afektif;
use Illuminate\Database\Eloquent\Factories\Factory;
use Database\Factories\Kewaliasuhan\Wali_asuhFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Catatan_afektif>
 */
class CatatanAfektifFactory extends Factory
{
    protected $model = Catatan_afektif::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_santri' =>  Santri::inRandomOrder()->first()->id ?? Santri::factory(),
            'id_wali_asuh' => (new Wali_asuhFactory())->create()->id,
            'kepedulian_nilai' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'kepedulian_tindak_lanjut' => $this->faker->sentence(),
            'kebersihan_nilai' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'kebersihan_tindak_lanjut' => $this->faker->sentence(),
            'akhlak_nilai' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'akhlak_tindak_lanjut' => $this->faker->sentence(),
            'created_by' => 1,
            'updated_by' => 1,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
