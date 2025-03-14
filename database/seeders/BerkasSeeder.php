<?php

namespace Database\Seeders;

use App\Models\Berkas;
use App\Models\Biodata;
use App\Models\JenisBerkas;
use Illuminate\Database\Seeder;
use Database\Factories\BerkasFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BerkasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // (new BerkasFactory())->count(5)->create();
        $biodatas = Biodata::all(); // Ambil semua data biodata (25 data)
        $jenisBerkas = JenisBerkas::all(); // Ambil semua jenis berkas (18 data)
        $dataBerkas = [];

        foreach ($biodatas as $biodata) { 
            foreach ($jenisBerkas as $jenis) { 
                $dataBerkas[] = [
                    'id_biodata' => $biodata->id,
                    'id_jenis_berkas' => $jenis->id,
                    'file_path' => 'storage/berkas/' . uniqid() . '.jpg',
                    'created_by' => 1, // Sesuaikan dengan ID pengguna
                    'status' => 1, 
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Batch insert agar lebih cepat dan efisien
        Berkas::insert($dataBerkas);

        // Debugging: Tampilkan jumlah data yang telah disimpan
        echo "Total data di tabel berkas: " . Berkas::count() . "\n";
    }
}
