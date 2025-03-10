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
            // 'image_url' => $this->faker->imageUrl(200, 200, 'people'),
            'image_url' => Arr::random([
                'https://t4.ftcdn.net/jpg/03/64/21/11/360_F_364211147_1qgLVxv1Tcq0Ohz3FawUfrtONzz8nq3e.jpg',
                'https://shotkit.com/wp-content/uploads/2021/06/Cool-profile-picture-Zoom.jpg',
                'https://plus.unsplash.com/premium_photo-1689977968861-9c91dbb16049?fm=jpg&q=60&w=3000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8cHJvZmlsZSUyMHBpY3R1cmV8ZW58MHx8MHx8fDA%3D',
                'https://media.istockphoto.com/id/1437816897/photo/business-woman-manager-or-human-resources-portrait-for-career-success-company-we-are-hiring.jpg?s=612x612&w=0&k=20&c=tyLvtzutRh22j9GqSGI33Z4HpIwv9vL_MZw_xOE19NQ='
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
