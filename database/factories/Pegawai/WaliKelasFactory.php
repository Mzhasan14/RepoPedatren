<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\WaliKelas;
use Database\Factories\Pendidikan\RombelFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\Walikelas>
 */
class WaliKelasFactory extends Factory
{
    protected $model = WaliKelas::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pengajar' => (new PengajarFactory())->create()->id,
            'jumlah_murid' => $this->faker->numberBetween(20, 40),
            'created_by' => 1,
            'updated_by' => null,
            'status' => $this->faker->boolean,
        ];
    }
}
