<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\JenisBerkas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JenisBerkas>
 */
class JenisBerkasFactory extends Factory
{
   /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = JenisBerkas::class;
    
    public function definition(): array
    {
        return [
            'nama_jenis_berkas' => $this->faker->word,
            'created_by' => User::factory(),
            'updated_by' => null,
            'deleted_by' => null,
            'status' => true,
        ];
    }
}
