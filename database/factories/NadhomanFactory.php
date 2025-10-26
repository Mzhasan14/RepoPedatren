<?php

namespace Database\Factories;

use App\Models\Kitab;
use App\Models\Nadhoman;
use App\Models\Santri;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Nadhoman>
 */
class NadhomanFactory extends Factory
{
    protected $model = Nadhoman::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'santri_id'       => Santri::where('status', 'aktif')->inRandomOrder()->value('id'),
            'kitab_id'        => Kitab::inRandomOrder()->value('id'),
            'tahun_ajaran_id' => TahunAjaran::inRandomOrder()->value('id'),
            'tanggal'         => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'jenis_setoran'   => $this->faker->randomElement(['baru', 'murojaah']),
            'bait_mulai'      => $this->faker->numberBetween(1, 50),
            'bait_selesai'    => $this->faker->numberBetween(51, 100),
            'nilai'           => $this->faker->randomElement(['lancar', 'cukup', 'kurang']),
            'catatan'         => $this->faker->optional()->sentence(),
            'status'          => $this->faker->randomElement(['proses', 'tuntas']),
            'created_by'      => 1
        ];
    }
}
