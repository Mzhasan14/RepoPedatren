<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\MataPelajaran;
use App\Models\Pegawai\Pengajar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MataPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mapelList = [
            'Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika',
            'Kimia', 'Biologi', 'Sejarah', 'Ekonomi', 'Geografi',
            'Pendidikan Agama', 'Seni Budaya', 'PJOK',
            'Sosiologi', 'Antropologi', 'TIK', 'Kewirausahaan',
            'Bimbingan Konseling', 'Bahasa Arab', 'Bahasa Jawa',
            'Akuntansi', 'Manajemen', 'Pemrograman', 'Desain Grafis',
            'Multimedia', 'Robotika', 'Statistika', 'Logika Matematika',
            'Etika Profesi', 'Psikologi Pendidikan'
        ];

        $kodeIndex = 1;

        $pengajarList = Pengajar::all();

        foreach ($pengajarList as $pengajar) {
            $jumlahMapel = rand(1, 2); // tiap pengajar dapat 1 atau 2 mapel

            for ($i = 0; $i < $jumlahMapel && $kodeIndex <= count($mapelList); $i++) {
                MataPelajaran::create([
                    'kode_mapel' => 'MP-' . str_pad($kodeIndex, 3, '0', STR_PAD_LEFT),
                    'nama_mapel' => $mapelList[$kodeIndex - 1],
                    'pengajar_id' => $pengajar->id,
                    'status' => true,
                    'created_by' => 1,
                ]);
                $kodeIndex++;
            }

            if ($kodeIndex > count($mapelList)) {
                break; // stop kalau mapel habis
            }
        }
    }
}
