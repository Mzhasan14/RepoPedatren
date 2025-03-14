<?php

namespace Database\Factories;

use Illuminate\Support\Arr;
use Database\Factories\Alamat\DesaFactory;
use Database\Factories\Alamat\KabupatenFactory;
use Database\Factories\Alamat\KecamatanFactory;
use Database\Factories\Alamat\NegaraFactory;
use Database\Factories\Alamat\ProvinsiFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Biodata>
 */
class BiodataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_negara' =>  (new NegaraFactory())->create()->id,
            'id_provinsi' =>  (new ProvinsiFactory())->create()->id,
            'id_kabupaten' =>  (new KabupatenFactory())->create()->id,
            'id_kecamatan' =>  (new KecamatanFactory())->create()->id,
            'id_desa' =>  (new DesaFactory())->create()->id,
            'nama' => $this->faker->name(),
            'niup' => $this->faker->unique()->numerify('###########'),
            'jenis_kelamin' => $this->faker->randomElement(['l', 'p']),
            'tanggal_lahir' => $this->faker->date(),
            'tempat_lahir' => $this->faker->city(),
            'nik' => $this->faker->unique()->numerify('###############'),
            'no_kk' => $this->faker->numerify('###############'),
            'no_telepon' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'jenjang_pendidikan_terakhir' => $this->faker->randomElement(['paud', 'sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
            'nama_pendidikan_terakhir' => $this->faker->randomElement([
                'SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'
            ]),
            'anak_keberapa' => rand(1, 5),
            'dari_saudara' => rand(1, 5),
            'tinggal_bersama' => $this->faker->word,
            'smartcard' => $this->faker->uuid,
            'status' => $this->faker->boolean(),
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }
}
