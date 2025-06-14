<?php

namespace Database\Factories;

use App\Models\HubunganKeluarga;
use App\Models\Santri;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PengunjungMahrom>
 */
class PengunjungMahromFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'santri_id' => Santri::inRandomOrder()->value('id') ?? 1,
            'nama_pengunjung' => $this->faker->name,
            'hubungan_id' => HubunganKeluarga::inRandomOrder()->value('id') ?? 1,
            'jumlah_rombongan' => $this->faker->numberBetween(1, 5),
            'tanggal_kunjungan' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'created_by' => 1,
        ];
    }
}
