<?php

namespace Database\Factories\Kewaliasuhan;

use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Santri;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kewaliasuhan\Wali_asuh>
 */
class Wali_asuhFactory extends Factory
{
    protected $model = Wali_asuh::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_santri' => Santri::inRandomOrder()->first()->id ?? Santri::factory(),
            'id_grup_wali_asuh' => (new Grup_WaliAsuhhFactory)->create()->id,
            'tanggal_mulai' => now()->subYears(rand(1, 3)),
            'tanggal_berakhir' => null,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
