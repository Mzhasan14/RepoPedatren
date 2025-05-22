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
}
