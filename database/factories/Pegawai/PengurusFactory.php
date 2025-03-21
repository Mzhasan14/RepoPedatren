<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\Pengurus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\Pengurus>
 */
class PengurusFactory extends Factory
{
    protected $model = Pengurus::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pegawai' =>(new PegawaiFactory())->create()->id,
            'id_golongan' => (new GolonganFactory())->create()->id,
            'satuan_kerja' => $this->faker->company,
            'jabatan' => $this->faker->jobTitle,
            'keterangan_jabatan' => $this->faker->randomElement([
                'Pengasuh',
                'Ketua Dewan Pengasuh',
                'Wakil Ketua Pengasuh',
                'Sekretaris Dewan Pengasuh',
                'Anggota Dewan Pengasuh',
                'Guru Pembimbing',
                'Dosen Tamu'
            ]),
            'created_by' => 1,
            'status' => $this->faker->boolean,
        ];
    }
}
