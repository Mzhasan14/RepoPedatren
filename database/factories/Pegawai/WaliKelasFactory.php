<?php

namespace Database\Factories\Pegawai;
use Illuminate\Support\Str;
use App\Models\Pegawai\WaliKelas;
use Database\Factories\Pendidikan\JurusanFactory;
use Database\Factories\Pendidikan\KelasFactory;
use Database\Factories\Pendidikan\LembagaFactory;
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
            'pegawai_id' => (new PegawaiFactory())->create()->id,
            'lembaga_id' =>  (new LembagaFactory())->create()->id,
            'jurusan_id' =>  (new JurusanFactory())->create()->id,
            'kelas_id' =>  (new KelasFactory())->create()->id,
            'rombel_id' =>  (new RombelFactory())->create()->id,
            'jumlah_murid' => $this->faker->numberBetween(20, 40),
            'created_by' => 1,
            'updated_by' => null,
            'status_aktif' => $this->faker->randomElement(['aktif', 'tidak aktif']),
            
        ];
    }
}
