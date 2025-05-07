<?php

namespace Database\Factories\Pegawai;
use Illuminate\Support\Str;
use App\Models\Pegawai\Pengajar;
use Database\Factories\Pendidikan\JurusanFactory;
use Database\Factories\Pendidikan\KelasFactory;
use Database\Factories\Pendidikan\LembagaFactory;
use Database\Factories\Pendidikan\RombelFactory;
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
            'pegawai_id' => (new PegawaiFactory())->create()->id,
            'golongan_id' => (new GolonganFactory())->create()->id,
            'lembaga_id' =>  (new LembagaFactory())->create()->id,
            'jabatan' => $this->faker->randomElement(['kultural', 'tetap', 'kontrak', 'pengkaderan']),
            'tahun_masuk' => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'tahun_akhir' => $this->faker->boolean(70) 
                ? $this->faker->dateTimeBetween($this->faker->dateTimeBetween('-10 years', 'now'), 'now')->format('Y-m-d') 
                : null,
            'status_aktif' => $this->faker->randomElement(['aktif', 'tidak aktif']),
            'created_by' => 1,
            'updated_by' => null,
        ];
    }
}
