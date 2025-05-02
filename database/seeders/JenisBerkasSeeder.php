<?php

namespace Database\Seeders;

use App\Models\Berkas;
use App\Models\Biodata;
use App\Models\JenisBerkas;
use Illuminate\Database\Seeder;
use Database\Factories\JenisBerkasFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JenisBerkasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // (new JenisBerkasFactory())->count(5)->create();
        $jenisBerkas = [
            ['nama_jenis_berkas' => 'Kartu Keluarga (KK)', 'wajib' => 1],
            ['nama_jenis_berkas' => 'KTP/KIA', 'wajib' => 1],
            ['nama_jenis_berkas' => 'Akte Kelahiran', 'wajib' => 1],
            ['nama_jenis_berkas' => 'Pas Foto', 'wajib' => 1],
            ['nama_jenis_berkas' => 'Ijazah Terakhir', 'wajib' => 1],
            ['nama_jenis_berkas' => 'Fotokopi Rapor Terakhir', 'wajib' => 1],
            ['nama_jenis_berkas' => 'Surat Keterangan Sehat', 'wajib' => 1],
            ['nama_jenis_berkas' => 'BPJS / Kartu Asuransi Kesehatan', 'wajib' => 0],
            ['nama_jenis_berkas' => 'Surat Pernyataan Kesanggupan', 'wajib' => 1],
            ['nama_jenis_berkas' => 'Surat Izin Orang Tua', 'wajib' => 1],
            ['nama_jenis_berkas' => 'Surat Pernyataan Tidak Merokok', 'wajib' => 0],
            ['nama_jenis_berkas' => 'Surat Keterangan Pindah Sekolah', 'wajib' => 0],
            ['nama_jenis_berkas' => 'Surat Keterangan Lulus (SKL)', 'wajib' => 1],
            ['nama_jenis_berkas' => 'Surat Rekomendasi dari Ulama/Guru', 'wajib' => 0],
            ['nama_jenis_berkas' => 'Surat Pernyataan Bebas Narkoba', 'wajib' => 1],
            ['nama_jenis_berkas' => 'Surat Domisili (jika dari luar kota)', 'wajib' => 0],
            ['nama_jenis_berkas' => 'Surat Keterangan Anak Yatim/Piatu', 'wajib' => 0],
            ['nama_jenis_berkas' => 'Fotokopi Kartu Santri', 'wajib' => 0],
            ['nama_jenis_berkas' => 'Bukti Izin', 'wajib' => 0],
        ];

        foreach ($jenisBerkas as $berkas) {
            JenisBerkas::create([
                'nama_jenis_berkas' => $berkas['nama_jenis_berkas'],
                'wajib' => $berkas['wajib'],
                'created_by' => 1,
                'updated_by' => null,
                'deleted_by' => null,
                'status' => 1,
            ]);
        }
    }
}
