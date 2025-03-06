<?php

namespace Database\Factories\Kewaliasuhan;

use Database\Factories\Peserta_didikFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kewaliasuhan\Anak_Asuh>
 */
class Anak_AsuhFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_peserta_didik' => (new Peserta_didikFactory())->create()->id,
            'id_grup_wali_asuh' => (new Grup_WaliAsuhhFactory())->create()->id,
            'created_by' => 1,
            'updated_by' => null,
            'status' => true,
        ];
    }
}
