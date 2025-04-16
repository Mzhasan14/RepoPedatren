<?php

namespace Database\Factories\Pegawai;

use App\Models\Biodata;
use Illuminate\Support\Str;
use App\Models\Pegawai\Pegawai;
use Database\Factories\Pendidikan\KelasFactory;
use Database\Factories\Pendidikan\RombelFactory;
use Database\Factories\Pendidikan\JurusanFactory;
use Database\Factories\Pendidikan\LembagaFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\Pegawai>
 */
class PegawaiFactory extends Factory
{
    protected $model = Pegawai::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'id_biodata' => Biodata::inRandomOrder()->first()?->id,
            'warga_pesantren' => $this->faker->boolean,
            'id_lembaga' =>  (new LembagaFactory())->create()->id,
            'id_jurusan' =>  (new JurusanFactory())->create()->id,
            'id_kelas' =>  (new KelasFactory())->create()->id,
            'id_rombel' =>  (new RombelFactory())->create()->id,
            'created_by' => 1,
            'updated_by' => null,
            'status' => $this->faker->boolean(),
        ];
    }
}
