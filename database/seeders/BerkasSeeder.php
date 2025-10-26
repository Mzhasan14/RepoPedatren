<?php

namespace Database\Seeders;

use App\Models\Berkas;
use App\Models\Biodata;
use App\Models\JenisBerkas;
use Illuminate\Database\Seeder;

class BerkasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisBerkas = JenisBerkas::all(); // Ambil semua jenis berkas (18 data)

        if ($jenisBerkas->isEmpty()) {
            echo "Jenis berkas kosong. Tidak ada data yang dimasukkan.\n";

            return;
        }

        $dataBerkas = [];

        Biodata::chunk(100, function ($biodatas) use ($jenisBerkas, &$dataBerkas) {
            foreach ($biodatas as $biodata) {
                foreach ($jenisBerkas as $jenis) {
                    $dataBerkas[] = [
                        'biodata_id' => $biodata->id,
                        'jenis_berkas_id' => $jenis->id,
                        'file_path' => 'storage/berkas/'.uniqid().'.jpg',
                        'created_by' => 1, // Sesuaikan dengan ID pengguna
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Insert setiap batch untuk menghindari penggunaan memori berlebihan
            if (! empty($dataBerkas)) {
                Berkas::insert($dataBerkas);
                $dataBerkas = []; // Kosongkan array setelah insert
            }
        });

        // Debugging: Tampilkan jumlah data yang telah disimpan
        echo 'Total data di tabel berkas: '.Berkas::count()."\n";

    }
}
