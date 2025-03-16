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
<<<<<<< HEAD
=======
            // 'id_rombel' => (new RombelFactory())->create()->id,
>>>>>>> a4c57d2c63bca651ab21fa414a3af6adefa21ac5
            'jumlah_murid' => $this->faker->numberBetween(20, 40),
            'created_by' => 1,
            'updated_by' => null,
            'status' => $this->faker->boolean,
        ];
    }
}
