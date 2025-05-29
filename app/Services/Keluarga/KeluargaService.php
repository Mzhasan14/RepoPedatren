<?php

namespace App\Services\Keluarga;

use App\Models\Keluarga;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class KeluargaService
{
    public function update(array $data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $keluarga = Keluarga::findOrFail($id);

            $keluarga->update([
                'no_kk' => $data['no_kk'] ?? $keluarga->no_kk,
                'updated_by' => Auth::id(),
            ]);

            activity('keluarga_update')
                ->performedOn($keluarga)
                ->withProperties(['new' => $keluarga->getAttributes()])
                ->event('update_keluarga')
                ->log('Data keluarga berhasil diubah');

            return [
                'status' => true,
                'message' => 'Data keluarga berhasil diperbarui',
                'data' => $keluarga,
            ];
        });
    }

    public function show(int $id): array
    {
        $rp = Keluarga::with(['bio', 'jurusan', 'kelas', 'rombel', 'angkatan'])->find($id);
        if (! $rp) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        return [
            'status' => true,
            'data'   => [
                'id'             => $rp->id,
                'no_induk'       => $rp->no_induk,
                'nama_lembaga'   => $rp->lembaga->nama_lembaga,
                'nama_jurusan'   => $rp->jurusan->nama_jurusan,
                'nama_kelas'     => $rp->kelas->nama_kelas,
                'nama_rombel'    => $rp->rombel->nama_rombel,
                'nama_angkatan'  => $rp->angkatan ? $rp->angkatan->nama_angkatan : null,
                'tanggal_masuk'  => $rp->tanggal_masuk,
                'tanggal_keluar' => $rp->tanggal_keluar,
                'status'         => $rp->status,
            ],
        ];
    }

}