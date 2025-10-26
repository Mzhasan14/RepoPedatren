<?php

namespace Database\Factories;

use App\Models\Alamat\Kabupaten;
use App\Models\Alamat\Kecamatan;
use App\Models\Alamat\Negara;
use App\Models\Alamat\Provinsi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            // Ambil negara random yg sudah ada
            'negara_id' => function () {
                return Negara::inRandomOrder()->first()->id;
            },

            // Ambil provinsi random yg sesuai dengan negara_id di atas
            'provinsi_id' => function (array $attributes) {
                return Provinsi::where('negara_id', $attributes['negara_id'])->inRandomOrder()->first()->id;
            },

            // Ambil kabupaten random yg sesuai dengan provinsi_id di atas
            'kabupaten_id' => function (array $attributes) {
                return Kabupaten::where('provinsi_id', $attributes['provinsi_id'])->inRandomOrder()->first()->id;
            },

            // Ambil kecamatan random yg sesuai dengan kabupaten_id di atas
            'kecamatan_id' => function (array $attributes) {
                return Kecamatan::where('kabupaten_id', $attributes['kabupaten_id'])->inRandomOrder()->first()->id;
            },
            'jalan' => $this->faker->streetAddress,
            'kode_pos' => $this->faker->postcode,
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
                'SD',
                'SMP',
                'SMA',
                'D3',
                'S1',
                'S2',
                'S3',
            ]),
            'anak_keberapa' => rand(1, 5),
            'dari_saudara' => rand(1, 5),
            'tinggal_bersama' => $this->faker->word,
            // 'smartcard' => $this->faker->uuid,
            'status' => $this->faker->boolean(),
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }
}
