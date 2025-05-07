<?php

namespace App\Services\PesertaDidik\Formulir;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BerkasService
{
    public function index($bioId)
    {
        $berkas = DB::table('berkas as br')
            ->join('jenis_berkas as jk', 'br.jenis_berkas_id', 'jk.id')
            ->where('br.biodata_id', $bioId)
            ->select(
                'br.id',
                'br.file_path',
                'jk.nama_jenis_berkas'
            )
            ->get();

        if (!$berkas) {
            return ['status' => false, 'message' => 'tidak memiliki berkas'];
        }

        return ['status' => true, 'data' => $berkas];
    }

    public function edit($id)
    {
        $berkas = DB::table('berkas as br')
            ->join('jenis_berkas as jk', 'br.jenis_berkas_id', 'jk.id')
            ->where('br.id', $id)
            ->select(
                'br.id',
                'br.file_path',
                'br.jenis_berkas_id',
                'jk.nama_jenis_berkas'
            )
            ->first();

        if (!$berkas) {
            return ['status' => false, 'message' => 'tidak memiliki berkas'];
        }

        return ['status' => true, 'data' => $berkas];
    }

    public function update(array $berkasData, string $berkasId)
    {
        // Ambil record berkas berdasarkan id_berkas
        $existing = DB::table('berkas')->where('id', $berkasId)->first();
        if (! $existing) {
            return ['status' => false, 'message' => "Berkas dengan ID #{$berkasId} tidak ditemukan"];
        }

        // Validasi file yang diunggah
        if (empty($berkasData['file_path']) || ! $berkasData['file_path'] instanceof UploadedFile) {
            return ['status' => false, 'message' => 'Data berkas tidak valid.'];
        }

        // Hapus file lama jika ada
        if (! empty($existing->file_path) && Storage::disk('public')->exists($existing->file_path)) {
            Storage::disk('public')->delete($existing->file_path);
        }

        // Simpan file baru di disk public
        $uploadedFile = $berkasData['file_path'];
        $filePath     = $uploadedFile->store('PesertaDidik', 'public');
        $fileUrl      = Storage::url($filePath);

        // Update record berkas
        DB::table('berkas')
            ->where('id', $berkasId)
            ->update([
                'file_path'  => $fileUrl,
                'status'     => true,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

        // Ambil kembali data yang telah diupdate untuk response
        $updated = DB::table('berkas as br')
            ->join('jenis_berkas as jk', 'br.jenis_berkas_id', 'jk.id')
            ->where('br.id', $berkasId)
            ->select(
                'br.id',
                'br.file_path',
                'jk.nama_jenis_berkas'
            )
            ->first();

        return ['status' => true, 'data' => $updated];
    }
}
