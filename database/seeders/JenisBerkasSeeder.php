<?php

namespace Database\Seeders;

use App\Models\JenisBerkas;
use Database\Factories\JenisBerkasFactory;
use Illuminate\Database\Seeder;

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
            ['nama_jenis_berkas' => 'Kartu Keluarga (KK)'],
            ['nama_jenis_berkas' => 'KTP/KIA'],
            ['nama_jenis_berkas' => 'Akte Kelahiran'],
            ['nama_jenis_berkas' => 'Pas Foto'],
            ['nama_jenis_berkas' => 'Ijazah Terakhir'],
            ['nama_jenis_berkas' => 'Fotokopi Rapor Terakhir'],
            ['nama_jenis_berkas' => 'Surat Keterangan Sehat'],
            ['nama_jenis_berkas' => 'BPJS / Kartu Asuransi Kesehatan'],
            ['nama_jenis_berkas' => 'Surat Pernyataan Kesanggupan'],
            ['nama_jenis_berkas' => 'Surat Izin Orang Tua'],
            ['nama_jenis_berkas' => 'Surat Pernyataan Tidak Merokok'],
            ['nama_jenis_berkas' => 'Surat Keterangan Pindah Sekolah'],
            ['nama_jenis_berkas' => 'Surat Keterangan Lulus (SKL)'],
            ['nama_jenis_berkas' => 'Surat Rekomendasi dari Ulama/Guru'],
            ['nama_jenis_berkas' => 'Surat Pernyataan Bebas Narkoba'],
            ['nama_jenis_berkas' => 'Surat Domisili (jika dari luar kota)'],
            ['nama_jenis_berkas' => 'Surat Keterangan Anak Yatim/Piatu'],
            ['nama_jenis_berkas' => 'Fotokopi Kartu Santri'],
        ];

        foreach ($jenisBerkas as $berkas) {
            JenisBerkas::create([
                'nama_jenis_berkas' => $berkas['nama_jenis_berkas'],
                'created_by' => 1,
                'updated_by' => null,
                'deleted_by' => null,
                'status' => 1,
            ]);
        }
    }
}
