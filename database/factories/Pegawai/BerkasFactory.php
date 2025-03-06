<?php

namespace Database\Factories\Pegawai;

use App\Models\Biodata;
use App\Models\Pegawai\Berkas;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\Berkas>
 */
class BerkasFactory extends Factory
{
    protected $model = Berkas::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_biodata' => Biodata::inRandomOrder()->first()?->id,
            'id_jenis_berkas' => (new JenisBerkasFactory())->create()->id,
            'file_path' => Arr::random([
                'https://t4.ftcdn.net/jpg/03/64/21/11/360_F_364211147_1qgLVxv1Tcq0Ohz3FawUfrtONzz8nq3e.jpg',
                'https://shotkit.com/wp-content/uploads/2021/06/Cool-profile-picture-Zoom.jpg',
                'https://plus.unsplash.com/premium_photo-1689977968861-9c91dbb16049?fm=jpg&q=60&w=3000&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8cHJvZmlsZSUyMHBpY3R1cmV8ZW58MHx8MHx8fDA%3D',
                'https://media.istockphoto.com/id/1437816897/photo/business-woman-manager-or-human-resources-portrait-for-career-success-company-we-are-hiring.jpg?s=612x612&w=0&k=20&c=tyLvtzutRh22j9GqSGI33Z4HpIwv9vL_MZw_xOE19NQ='
            ]),
            'created_by' => 1,
            'updated_by' => null,
            'status' => $this->faker->boolean,
        ];
    }
}
