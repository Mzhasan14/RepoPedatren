<?php

namespace App\Services\PesertaDidik\Formulir;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BerkasService
{
    public function update(array $berkasData, string $santriId)
    {
        // Ambil biodata_id berdasarkan santriId
        $biodataId = DB::table('santri')
            ->where('id', $santriId)
            ->value('biodata_id');

        // Jika tidak ada biodata_id, lempar exception
        if (! $biodataId) {
            throw new \Exception("Santri #{$santriId} tidak memiliki biodata_id.");
        }

        // Pastikan 'berkas' adalah array
        if (empty($berkasData['berkas']) || !is_array($berkasData['berkas'])) {
            throw new \Exception("Data berkas tidak valid.");
        }

        foreach ($berkasData['berkas'] as $berkas) {  // Loop melalui array berkas
            // Pastikan setiap berkas memiliki jenis_berkas_id
            if (empty($berkas['jenis_berkas_id'])) {
                throw new \Exception("Key jenis_berkas_id tidak ditemukan dalam data berkas.");
            }

            $jenisBerkasId = (int) $berkas['jenis_berkas_id'];  // Pastikan casting ke integer

            // Pastikan file_path ada dan valid
            if (empty($berkas['file_path']) || !($berkas['file_path'] instanceof UploadedFile)) {
                throw new \Exception("Data berkas tidak valid.");
            }

            $uploadedFile = $berkas['file_path'];

            // Cek apakah berkas dengan jenis_berkas_id sudah ada
            $existing = DB::table('berkas')
                ->where('biodata_id', $biodataId)
                ->where('jenis_berkas_id', $jenisBerkasId)
                ->first();

            // Jika berkas sudah ada, hapus file lama
            if ($existing && !empty($existing->file_path) && Storage::disk('public')->exists($existing->file_path)) {
                Storage::disk('public')->delete($existing->file_path);
            }

            // Simpan file baru di disk public (storage/app/public)
            $filePath = $uploadedFile->store('PesertaDidik', 'public');

            // Membuat URL yang sesuai
            $fileUrl = Storage::url($filePath);  // Ini akan menghasilkan /storage/PesertaDidik/... sesuai dengan symbolic link

            // Update atau insert data berkas
            DB::table('berkas')->updateOrInsert(
                [
                    'biodata_id' => $biodataId,
                    'jenis_berkas_id' => $jenisBerkasId,
                ],
                [
                    'file_path' => $fileUrl,  // Simpan URL yang benar
                    'status' => true,
                    'created_by' => Auth::id(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
