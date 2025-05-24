<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Berkas;
use App\Models\JenisBerkas;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BerkasService
{
    public function index(string $biodataId): array
    {
        $br = Berkas::with('jenisBerkas:id,nama_jenis_berkas')
            ->where('biodata_id', $biodataId)
            ->get();

        if ($br->isEmpty()) {
            return [
                'status'  => false,
                'message' => 'Biodata tidak memiliki berkas.',
            ];
        }

        $data = $br->map(fn(Berkas $br) => [
            'id'                 => $br->id,
            'file_path'          => url($br->file_path),
            'jenis_berkas_id'    => $br->jenis_berkas_id,
            'nama_jenis_berkas'  => $br->jenisBerkas?->nama_jenis_berkas, // null-safe
        ])->toArray();

        return [
            'status' => true,
            'data'   => $data,
        ];
    }

    public function show(int $id): array
    {
        $br = Berkas::with('jenisBerkas:id,nama_jenis_berkas')->find($id);

        if (! $br) {
            return [
                'status'  => false,
                'message' => "Berkas dengan ID #{$id} tidak ditemukan.",
            ];
        }

        return [
            'status' => true,
            'data'   => [
                'id'                 => $br->id,
                'file_path'          =>  url($br->file_path),
                'jenis_berkas_id'    => $br->jenis_berkas_id,
                'nama_jenis_berkas'  => $br->jenisBerkas?->nama_jenis_berkas, // aman dari null
            ],
        ];
    }

    public function store(array $input, string $biodataId): array
    {
        return DB::transaction(function () use ($input, $biodataId) {
            // Validasi berkas
            if (empty($input['file']) || ! $input['file'] instanceof UploadedFile) {
                return [
                    'status'  => false,
                    'message' => 'File tidak valid.',
                ];
            }

            // Validasi jenis berkas
            $jenisId = $input['jenis_berkas_id'] ?? null;
            if (! $jenisId || ! JenisBerkas::where('id', $jenisId)->exists()) {
                return [
                    'status'  => false,
                    'message' => 'Jenis berkas tidak ditemukan.',
                ];
            }

            // Simpan file
            $uploadedFile = $input['file'];
            $path = $uploadedFile->store('formulir', 'public');

            // Buat record
            $br = Berkas::create([
                'biodata_id'        => $biodataId,
                'jenis_berkas_id'   => $jenisId,
                'file_path'         => Storage::url($path),
                'status'            => true,
                'created_by'        => Auth::id(),
            ]);

            return [
                'status' => true,
                'data'   => $br,
            ];
        });
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $br = Berkas::find($id);
            if (! $br) {
                return [
                    'status'  => false,
                    'message' => "Berkas dengan ID #{$id} tidak ditemukan.",
                ];
            }

            // Validasi berkas baru
            if (empty($input['file']) || ! $input['file'] instanceof UploadedFile) {
                return [
                    'status'  => false,
                    'message' => 'File tidak valid.',
                ];
            }

            // Validasi jenis berkas jika diinput
            $jenisId = $input['jenis_berkas_id'] ?? $br->jenis_berkas_id;
            if (! JenisBerkas::where('id', $jenisId)->exists()) {
                return [
                    'status'  => false,
                    'message' => 'Jenis berkas tidak ditemukan.',
                ];
            }

            // Hapus file lama jika ada
            $oldPath = str_replace(Storage::url(''), '', $br->file_path);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            // Simpan file baru
            $uploadedFile = $input['file'];
            $path = $uploadedFile->store('formulir', 'public');

            // Update record
            $br->file_path        = Storage::url($path);
            $br->jenis_berkas_id  = $jenisId;
            $br->updated_by       = Auth::id();
            $br->save();

            return [
                'status' => true,
                'data'   => $br,
            ];
        });
    }
}
