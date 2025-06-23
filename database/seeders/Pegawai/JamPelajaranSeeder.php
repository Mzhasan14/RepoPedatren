<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\JamPelajaran;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JamPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $start = Carbon::createFromTime(7, 0); // mulai jam 07:00

        for ($i = 1; $i <= 10; $i++) {
            $jamMulai = $start->copy();
            $jamSelesai = $start->copy()->addMinutes(40);

            JamPelajaran::create([
                'jam_ke' => $i,
                'label' => 'Jam ' . $i,
                'jam_mulai' => $jamMulai->format('H:i:s'),
                'jam_selesai' => $jamSelesai->format('H:i:s'),
                'created_by' => 1,
            ]);

            $start->addMinutes(45); // tambah durasi + waktu istirahat (misalnya 5 menit)
        }
    }
}
