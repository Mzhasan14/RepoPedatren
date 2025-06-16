<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\Pegawai;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DropPegawaiNoEntitas extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pegawai::whereDoesntHave('pengajar')
            ->whereDoesntHave('wali_Kelas')
            ->whereDoesntHave('karyawan')
            ->whereDoesntHave('pengurus')
            ->delete();
    }
}
