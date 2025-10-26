<?php

namespace Database\Factories\Pegawai;

use App\Models\Pegawai\JamPelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai\JamPelajaran>
 */
class JamPelajaranFactory extends Factory
{
    protected $model = JamPelajaran::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'jam_ke' => 0,
            'label' => null,
            'jam_mulai' => now()->format('H:i:s'),
            'jam_selesai' => now()->addMinutes(40)->format('H:i:s'),
            'created_by' => 1,
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }
}
