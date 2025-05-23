<?php

namespace Database\Factories\Pegawai;

use App\Models\Biodata;
use Illuminate\Support\Str;
use App\Models\Pegawai\Pegawai;
use Database\Factories\BiodataFactory;
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
            'biodata_id' => null, // wajib diisi manual dari Seeder
            'created_by' => 1,
            'status_aktif' => 'tidak aktif', // default nonaktif
        ];
    }
}
