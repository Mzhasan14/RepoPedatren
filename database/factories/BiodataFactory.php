<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Database\Factories\Alamat\NegaraFactory;
use Database\Factories\Alamat\ProvinsiFactory;
use Database\Factories\Alamat\KabupatenFactory;
use Database\Factories\Alamat\KecamatanFactory;
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
            'id' => (string) Str::uuid(),
            'negara_id' =>  (new NegaraFactory())->create()->id,
            'provinsi_id' =>  (new ProvinsiFactory())->create()->id,
            'kabupaten_id' =>  (new KabupatenFactory())->create()->id,
            'kecamatan_id' =>  (new KecamatanFactory())->create()->id,
            'jalan' =>  $this->faker->streetAddress,
            'kode_pos' =>  $this->faker->postcode,
            'nama' => $this->faker->name(),
            'jenis_kelamin' => $this->faker->randomElement(['l', 'p']),
            'tanggal_lahir' => $this->faker->date(),
            'tempat_lahir' => $this->faker->city(),
            'nik' => $this->faker->unique()->numerify('################'),
            'no_telepon' => $this->faker->phoneNumber(),
            'no_telepon_2' => $this->faker->phoneNumber(),
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
        dd('test');
    }
}
