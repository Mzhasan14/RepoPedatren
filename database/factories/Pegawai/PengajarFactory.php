<?php

namespace Database\Factories\Pegawai;
use Illuminate\Support\Str;
use App\Models\Pegawai\Pengajar;
use Database\Factories\Pendidikan\LembagaFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\Pengajar>
 */
class PengajarFactory extends Factory
{
    protected $model = Pengajar::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'id_pegawai' => (new PegawaiFactory())->create()->id,
            'id_golongan' => (new GolonganFactory())->create()->id,
            'jabatan' => $this->faker->jobTitle,
            'tahun_masuk' => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
'tahun_keluar' => $this->faker->boolean(70) 
    ? $this->faker->dateTimeBetween($this->faker->dateTimeBetween('-10 years', 'now'), 'now')->format('Y-m-d') 
    : null,
            'created_by' => 1,
            'updated_by' => null,
            'status' => $this->faker->boolean(),
        ];
    }
}
