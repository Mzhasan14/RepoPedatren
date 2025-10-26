<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\Pegawai;
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
