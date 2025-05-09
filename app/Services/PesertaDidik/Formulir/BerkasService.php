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

    public function store(array $berkasData, string $biodataId)
    {
        return DB::transaction(function () use ($berkasData, $biodataId) {
            if (empty($berkasData['file_path']) || !$berkasData['file_path'] instanceof UploadedFile) {
                return ['status' => false, 'message' => 'Data berkas tidak valid.'];
            }

            if (empty($berkasData['jenis_berkas_id']) || !JenisBerkas::where('id', $berkasData['jenis_berkas_id'])->exists()) {
                return ['status' => false, 'message' => 'Jenis berkas tidak ditemukan.'];
            }

            $uploadedFile = $berkasData['file_path'];
            $filePath = $uploadedFile->store('formulir', 'public');
            $fileUrl  = Storage::url($filePath);

            $berkas = new Berkas();
            $berkas->biodata_id = $biodataId;
            $berkas->jenis_berkas_id = $berkasData['jenis_berkas_id'];
            $berkas->file_path = $fileUrl;
            $berkas->status = true;
            $berkas->created_by = Auth::id();
            $berkas->save();

            activity('berkas')
                ->causedBy(Auth::user())
                ->performedOn($berkas)
                ->withProperties([
                    'new_attributes' => $berkas->toArray(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('create')
                ->log("Menambah berkas baru untuk biodata ID: {$biodataId}");

            return [
                'status' => true,
                'data' => array_merge($berkas->toArray())
            ];
        });
    }


    public function update(array $berkasData, string $berkasId)
    {
        return DB::transaction(function () use ($berkasData, $berkasId) {
            $berkas = Berkas::find($berkasId);
            if (!$berkas) {
                return ['status' => false, 'message' => "Berkas dengan ID #{$berkasId} tidak ditemukan"];
            }

            $before = $berkas->toArray();

            if (empty($berkasData['file_path']) || !$berkasData['file_path'] instanceof UploadedFile) {
                return ['status' => false, 'message' => 'Data berkas tidak valid.'];
            }

            if (!empty($berkasData['jenis_berkas_id']) && !JenisBerkas::where('id', $berkasData['jenis_berkas_id'])->exists()) {
                return ['status' => false, 'message' => 'Jenis berkas tidak ditemukan.'];
            }

            if (!empty($berkas->file_path) && Storage::disk('public')->exists($berkas->file_path)) {
                Storage::disk('public')->delete($berkas->file_path);
            }

            $uploadedFile = $berkasData['file_path'];
            $filePath = $uploadedFile->store('formulir', 'public');
            $fileUrl  = Storage::url($filePath);

            $berkas->file_path = $fileUrl;
            $berkas->jenis_berkas_id = $berkasData['jenis_berkas_id'];
            $berkas->status = true;
            $berkas->updated_by = Auth::id();

            $berkas->save();

            $after = $berkas->toArray();

            activity('berkas')
                ->causedBy(Auth::user())
                ->performedOn($berkas)
                ->withProperties([
                    'before' => $before,
                    'after' => $after,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('update')
                ->log("Mengubah berkas dengan ID: {$berkasId}");

            return [
                'status' => true,
                'data' => array_merge($after)
            ];
        });
    }
}
